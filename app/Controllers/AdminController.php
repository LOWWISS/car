<?php
/**
 * AdminController: admin dashboard, user management, listing approvals, reports.
 *
 * SECURITY (RBAC): every method requires the 'admin' role via Middleware.
 */
final class AdminController extends BaseController
{
    public function index(): void
    {
        Middleware::requireRole('admin');
        $carModel = new Car();
        $userModel = new User();
        $auditModel = new AuditLog();

        $pending = $carModel->pendingApprovals();
        $users = $userModel->allWithCounts();
        $logs = $auditModel->recent(30);

        // Simple analytics
        $db = Database::getInstance();
        $totalCars = $carModel->count();
        $totalUsers = $userModel->count();
        $totalBids = (int) $db->query('SELECT COUNT(*) FROM bids')->fetchColumn();
        $activeAuctions = (int) $db->query("SELECT COUNT(*) FROM cars WHERE status='active'")->fetchColumn();
        $grossGMV = (float) $db->query("SELECT COALESCE(SUM(bid_amount),0) FROM bids")->fetchColumn();

        $this->view('admin/index', [
            'pageTitle'       => 'Admin Dashboard',
            'pending'         => $pending,
            'users'           => $users,
            'logs'            => $logs,
            'analytics'       => [
                'total_cars'      => $totalCars,
                'total_users'     => $totalUsers,
                'total_bids'      => $totalBids,
                'active_auctions' => $activeAuctions,
                'gmv'             => $grossGMV,
            ],
        ]);
    }

    /**
     * Review a pending listing: shows the admin the full car content (gallery,
     * specs, description) BEFORE they decide to approve or reject. Only pending
     * listings can be reviewed here — non-pending listings redirect away.
     */
    public function review(string $id): void
    {
        Middleware::requireRole('admin');
        $carId = (int) $id;
        $carModel = new Car();
        $car = $carModel->getWithDetails($carId);
        if (!$car) Response::notFound('Listing not found.');
        if ($car['status'] !== 'pending') {
            $this->flashRedirect('error', 'Only pending listings can be reviewed.', '/admin');
        }
        $images = (new CarImage())->forCar($carId);
        $this->view('admin/review', [
            'pageTitle' => 'Review Listing: ' . $car['title'],
            'car'       => $car,
            'images'    => $images,
            'csrf'      => Csrf::token(),
        ]);
    }

    public function approve(string $id): void
    {
        Middleware::requireRole('admin');
        $this->guardPost('admin', [20, 60]);
        $carModel = new Car();
        $car = $carModel->find((int) $id);
        if (!$car) Response::notFound('Listing not found.');
        // Only pending listings can be approved — prevents re-activating a
        // sold/closed/rejected car back into a live auction.
        if ($car['status'] !== 'pending') {
            $this->flashRedirect('error', 'Only pending listings can be approved.', '/admin');
        }
        $carModel->setStatus((int) $id, 'active', Auth::id());
        (new Notification())->send(
            (int) $car['seller_id'],
            'Your listing "' . $car['title'] . '" was approved and is now live.',
            'listing', '/cars/view/' . $id
        );
        Logger::audit('listing_approve', ['car' => $id], Auth::id());
        $this->flashRedirect('success', 'Listing approved.', '/admin');
    }

    public function reject(string $id): void
    {
        Middleware::requireRole('admin');
        $this->guardPost('admin', [20, 60]);
        $carModel = new Car();
        $car = $carModel->find((int) $id);
        if (!$car) Response::notFound('Listing not found.');
        if ($car['status'] !== 'pending') {
            $this->flashRedirect('error', 'Only pending listings can be rejected.', '/admin');
        }
        $carModel->setStatus((int) $id, 'rejected', Auth::id());
        (new Notification())->send(
            (int) $car['seller_id'],
            'Your listing "' . $car['title'] . '" was rejected. Please review and resubmit.',
            'listing', '/dashboard'
        );
        Logger::audit('listing_reject', ['car' => $id], Auth::id());
        $this->flashRedirect('success', 'Listing rejected.', '/admin');
    }

    public function users(): void
    {
        Middleware::requireRole('admin');
        $users = (new User())->allWithCounts();
        $this->view('admin/users', ['pageTitle' => 'User Management', 'users' => $users]);
    }

    public function updateRole(string $id): void
    {
        Middleware::requireRole('admin');
        $this->guardPost('admin', [20, 60]);
        $role = (string) $this->request->input('role', 'buyer');
        if (!in_array($role, ['buyer', 'seller', 'admin'], true)) {
            $this->flashRedirect('error', 'Invalid role.', '/admin/users');
        }

        $userModel = new User();
        $target = $userModel->find((int) $id);
        if (!$target) Response::notFound('User not found.');

        // An admin cannot change their own role (self-lockout prevention).
        if ((int) $id === Auth::id()) {
            $this->flashRedirect('error', 'You cannot change your own role.', '/admin/users');
        }
        // An admin cannot demote or change another admin's role (prevent
        // lockout of the only remaining admins / privilege tampering).
        if ($target['role'] === 'admin') {
            $this->flashRedirect('error', 'Admin roles cannot be changed. Admins cannot be demoted to seller or buyer.', '/admin/users');
        }

        $userModel->updateRole((int) $id, $role);
        Logger::audit('user_role_change', ['user' => $id, 'role' => $role], Auth::id());
        $this->flashRedirect('success', 'User role updated.', '/admin/users');
    }

    public function toggleUser(string $id): void
    {
        Middleware::requireRole('admin');
        $this->guardPost('admin', [20, 60]);
        $userModel = new User();
        $user = $userModel->find((int) $id);
        if (!$user) Response::notFound('User not found.');
        $userModel->setActive((int) $id, !(bool) $user['is_active']);
        Logger::audit('user_toggle_active', ['user' => $id, 'active' => !(int)$user['is_active']], Auth::id());
        $this->flashRedirect('success', 'User status updated.', '/admin/users');
    }

    public function logs(): void
    {
        Middleware::requireRole('admin');
        $logs = (new AuditLog())->recent(200);
        $this->view('admin/logs', ['pageTitle' => 'Audit Logs', 'logs' => $logs]);
    }
}
