<?php

namespace Modules\Support\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\View;

class TicketController extends Controller {

    public function index() {
        $this->requireLogin();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$_SESSION['user']['id']]);
        $tickets = $stmt->fetchAll();

        View::render('modules/Support/views/index', ['tickets' => $tickets]);
    }

    public function create() {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            $priority = $_POST['priority'] ?? 'medium';

            if (!empty($subject) && !empty($message)) {
                $db = Database::getInstance();

                // Create Ticket
                $stmt = $db->prepare("INSERT INTO tickets (user_id, subject, priority) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user']['id'], $subject, $priority]);
                $ticketId = $db->lastInsertId();

                // Create First Message
                $stmt = $db->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
                $stmt->execute([$ticketId, $_SESSION['user']['id'], $message]);

                header('Location: /support');
                exit;
            }
        }

        View::render('modules/Support/views/create');
    }

    public function view($id) {
        $this->requireLogin();
        $db = Database::getInstance();

        // Fetch Ticket
        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user']['id']]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            header('Location: /support');
            exit;
        }

        // Handle Reply
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $_POST['message'] ?? '';
            if (!empty($message)) {
                $stmt = $db->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
                $stmt->execute([$id, $_SESSION['user']['id'], $message]);

                // Update ticket status
                $stmt = $db->prepare("UPDATE tickets SET status = 'open', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);

                header("Location: /support/view/$id");
                exit;
            }
        }

        // Fetch Messages
        $stmt = $db->prepare("SELECT tm.*, u.email, u.role FROM ticket_messages tm JOIN users u ON tm.user_id = u.id WHERE ticket_id = ? ORDER BY created_at ASC");
        $stmt->execute([$id]);
        $messages = $stmt->fetchAll();

        View::render('modules/Support/views/view', ['ticket' => $ticket, 'messages' => $messages]);
    }

    private function requireLogin() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
}
