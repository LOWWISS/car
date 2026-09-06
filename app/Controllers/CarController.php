<?php
/**
 * CarController: catalog, detail, create/edit/delete listings, image uploads.
 *
 * SECURITY:
 *  - IDOR prevention: edit/delete verify ownership (or admin) before acting.
 *  - File upload security: MIME + extension whitelist, size cap, random rename,
 *    stored under /public/uploads (non-executable dir via .htaccess).
 *  - RBAC: create/edit/delete require seller role; admin can approve/reject.
 *  - All catalog filters use prepared statements; sort columns whitelisted.
 */
final class CarController extends BaseController
{
    private Car $cars;
    private CarImage $images;

    public function __construct()
    {
        parent::__construct();
        $this->cars = new Car();
        $this->images = new CarImage();
    }

    /** Catalog with search/filter/sort/pagination. */
    public function catalog(): void
    {
        // Opportunistically close expired auctions (DB is source of truth).
        AuctionCloser::run();

        $allowedSorts = ['newest', 'oldest', 'price_low', 'price_high', 'ending', 'year_desc'];
        $sort = Validator::whitelist((string) $this->request->query('sort', 'newest'), $allowedSorts) ?? 'newest';

        $filters = [
            'q'         => trim((string) $this->request->query('q', '')),
            'make'      => trim((string) $this->request->query('make', '')),
            'body_type' => trim((string) $this->request->query('body_type', '')),
            'year'      => (string) $this->request->query('year', ''),
            'location'  => trim((string) $this->request->query('location', '')),
            'min_price' => $this->request->query('min_price', ''),
            'max_price' => $this->request->query('max_price', ''),
            'sort'      => $sort,
        ];

        $page = max(1, (int) $this->request->query('page', 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $result = $this->cars->search($filters, $perPage, $offset);
        $totalPages = max(1, (int) ceil($result['total'] / $perPage));

        $this->view('cars/catalog', [
            'pageTitle'  => 'Browse Cars',
            'cars'       => $result['rows'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'filters'    => $filters,
            'makes'      => $this->cars->distinctMakes(),
        ]);
    }

    /** Active auctions (bidding page), sorted by ending soon. */
    public function bidding(): void
    {
        $cars = $this->cars->endingSoon(24);
        $this->view('cars/bidding', [
            'pageTitle' => 'Live Bidding',
            'cars'      => $cars,
            'total'     => count($cars),
        ]);
    }

    /** Featured cars listing. */
    public function featured(): void
    {
        $cars = $this->cars->featured(24);
        $this->view('cars/featured', [
            'pageTitle' => 'Featured Cars',
            'cars'      => $cars,
            'total'     => count($cars),
        ]);
    }

    /** Car detail page with live bid panel. */
    public function show(string $id): void
    {
        $carId = (int) $id;
        $car = $this->cars->getWithDetails($carId);
        if (!$car) Response::notFound('Listing not found.');

        $images = $this->images->forCar($carId);
        $bidModel = new Bid();
        $history = $bidModel->historyForCar($carId, 20);
        $highest = $bidModel->highestBid($carId);

        $watching = false;
        if (Auth::check()) {
            $watching = (new Watchlist())->isWatching(Auth::id(), $carId);
        }

        $this->view('cars/detail', [
            'pageTitle' => e($car['title']),
            'car'       => $car,
            'images'    => $images,
            'history'   => $history,
            'highest'   => $highest,
            'watching'  => $watching,
            'csrf'      => Csrf::token(),
        ]);
    }

    public function createForm(): void
    {
        Middleware::requireRole('seller', 'admin');
        $this->view('cars/form', [
            'pageTitle' => 'Create Listing',
            'car'       => null,
            'images'    => [],
            'csrf'      => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        Middleware::requireRole('seller', 'admin');
        $this->guardPost('listing', [10, 300]);

        $d = $this->collectListingInput();
        $v = $this->validateListing($d);
        if (!$v->validate()) {
            $this->flashRedirect('error', implode(' ', $v->errors), '/cars/create');
        }
        if (strtotime($d['auction_end']) <= strtotime($d['auction_start'])) {
            $this->flashRedirect('error', 'Auction end must be after the start time.', '/cars/create');
        }

        $id = $this->cars->create($d, Auth::id());
        $this->handleUploads($id);
        Logger::audit('listing_create', ['car' => $id], Auth::id());

        // Notify all admins that a new listing is awaiting review.
        $notif = new Notification();
        foreach ((new User())->admins() as $admin) {
            $notif->send(
                (int) $admin['id'],
                'A new listing "' . $d['title'] . '" was submitted and is awaiting review.',
                'listing', '/admin'
            );
        }

        $this->flashRedirect('success', 'Listing submitted for admin approval.', '/cars/view/' . $id);
    }

    public function editForm(string $id): void
    {
        $carId = (int) $id;
        $car = $this->cars->find($carId);
        if (!$car) Response::notFound('Listing not found.');
        Middleware::requireOwnerOrAdmin((int) $car['seller_id'] === Auth::id());

        $this->view('cars/form', [
            'pageTitle' => 'Edit Listing',
            'car'       => $car,
            'images'    => $this->images->forCar($carId),
            'csrf'      => Csrf::token(),
        ]);
    }

    public function update(string $id): void
    {
        $carId = (int) $id;
        $car = $this->cars->find($carId);
        if (!$car) Response::notFound('Listing not found.');
        Middleware::requireOwnerOrAdmin((int) $car['seller_id'] === Auth::id());
        $this->guardPost('listing', [10, 300]);

        $d = $this->collectListingInput();
        $v = $this->validateListing($d);
        if (!$v->validate()) {
            $this->flashRedirect('error', implode(' ', $v->errors), '/cars/edit/' . $carId);
        }
        if (strtotime($d['auction_end']) <= strtotime($d['auction_start'])) {
            $this->flashRedirect('error', 'Auction end must be after the start time.', '/cars/edit/' . $carId);
        }
        $this->cars->update($carId, $d);
        $this->handleUploads($carId);
        Logger::audit('listing_update', ['car' => $carId], Auth::id());
        $this->flashRedirect('success', 'Listing updated.', '/cars/view/' . $carId);
    }

    public function delete(string $id): void
    {
        $carId = (int) $id;
        $car = $this->cars->find($carId);
        if (!$car) Response::notFound('Listing not found.');
        Middleware::requireOwnerOrAdmin((int) $car['seller_id'] === Auth::id());
        if (!Csrf::verify()) Response::redirect('/dashboard');

        // Remove image files from disk
        foreach ($this->images->forCar($carId) as $img) {
            $abs = dirname(__DIR__, 2) . '/public/' . $img['image_path'];
            if (is_file($abs)) @unlink($abs);
        }
        $this->cars->delete($carId);
        Logger::audit('listing_delete', ['car' => $carId], Auth::id());
        $this->flashRedirect('success', 'Listing deleted.', '/dashboard');
    }

    /**
     * Delete a single image. Returns JSON for AJAX requests, redirects back
     * for regular form POSTs (non-JS fallback). Ownership is verified before
     * deletion (IDOR prevention).
     */
    public function deleteImage(string $imageId): void
    {
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
               || ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';

        if (Auth::guest()) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Auth required.'], 401);
            $this->flashRedirect('error', 'You must be logged in to delete images.', '/login');
        }
        if (!Csrf::verify()) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Invalid CSRF token.'], 419);
            $this->flashRedirect('error', 'Invalid CSRF token. Please try again.', '/dashboard');
        }

        // Fetch first, verify ownership BEFORE deleting (IDOR prevention).
        $img = $this->images->find((int) $imageId);
        if (!$img) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Image not found.'], 404);
            Response::notFound('Image not found.');
        }

        $car = $this->cars->find((int) $img['car_id']);
        if (!$car) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Listing not found.'], 404);
            Response::notFound('Listing not found.');
        }

        // IDOR prevention — only the owner or an admin can delete.
        if ((int) $car['seller_id'] !== Auth::id() && !Auth::is('admin')) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'You do not own this resource.'], 403);
            Response::forbidden('You do not own this resource.');
        }

        $this->images->delete((int) $imageId);

        $abs = dirname(__DIR__, 2) . '/public/' . $img['image_path'];
        if (is_file($abs)) @unlink($abs);
        Logger::audit('image_delete', ['image' => $img['id']], Auth::id());

        if ($isAjax) {
            Response::json(['success' => true]);
        }
        // Non-AJAX: redirect back to the edit form with a success flash.
        $this->flashRedirect('success', 'Image deleted.', '/cars/edit/' . (int) $car['id']);
    }

    // ---------- helpers ----------

    private function collectListingInput(): array
    {
        return [
            'title'         => trim((string) $this->request->input('title', '')),
            'make'          => trim((string) $this->request->input('make', '')),
            'model'         => trim((string) $this->request->input('model', '')),
            'year'          => (string) $this->request->input('year', ''),
            'mileage'       => (string) $this->request->input('mileage', '0'),
            'body_type'     => trim((string) $this->request->input('body_type', 'Sedan')),
            'condition'     => trim((string) $this->request->input('condition', 'Used')),
            'location'      => trim((string) $this->request->input('location', '')),
            'description'   => trim((string) $this->request->input('description', '')),
            'starting_price'=> (string) $this->request->input('starting_price', '0'),
            'reserve_price' => (string) $this->request->input('reserve_price', ''),
            'buy_now_price' => (string) $this->request->input('buy_now_price', ''),
            'auction_start' => (string) $this->request->input('auction_start', date('Y-m-d H:i:s')),
            'auction_end'   => (string) $this->request->input('auction_end', ''),
        ];
    }

    private function validateListing(array $d): Validator
    {
        return new Validator($d, [
            'title'         => 'required|string|min:3|max:150',
            'make'          => 'required|string|max:60',
            'model'         => 'required|string|max:60',
            'year'          => 'required|integer',
            'mileage'       => 'required|integer',
            'body_type'     => 'required|in,Sedan,SUV,Pickup,Hatchback,Coupe,Convertible,Van,Wagon',
            'condition'     => 'required|in,New,Used,Certified',
            'location'      => 'string|max:100',
            'description'   => 'string|max:5000',
            'starting_price'=> 'required|numeric',
            'reserve_price' => 'numeric',
            'buy_now_price' => 'numeric',
            'auction_start' => 'required|datetime',
            'auction_end'   => 'required|datetime',
        ]);
    }

    /**
     * File upload security: validate MIME + extension whitelist, size cap,
     * random rename, store under /public/uploads (non-executable dir).
     */
    private function handleUploads(int $carId): void
    {
        if (empty($_FILES['images']['name'][0])) return;

        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $maxBytes = 5 * 1024 * 1024; // 5 MB
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);

        $files = $_FILES['images'];
        $count = count($files['name']);
        $madePrimary = (bool) $this->images->forCar($carId);

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($files['size'][$i] > $maxBytes) continue;

            // MIME from finfo (server-side, not trusting client)
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($files['tmp_name'][$i]);
            if (!isset($allowedMime[$mime])) continue;

            // Double-check extension against whitelist
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowedExt, true)) continue;
            if ($allowedMime[$mime] === 'jpg' && $ext === 'jpeg') $ext = 'jpg';

            // Random rename to prevent path traversal / overwrite attacks
            $newName = bin2hex(random_bytes(16)) . '.' . $ext;
            $dest = $uploadDir . '/' . $newName;
            if (!move_uploaded_file($files['tmp_name'][$i], $dest)) continue;

            $isPrimary = !$madePrimary;
            $this->images->add($carId, 'uploads/' . $newName, $isPrimary);
            $madePrimary = true;
        }
    }
}
