<?php
// models/Ticket.php

class Ticket {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($admin_id, $entreprise_id, $subject, $priority = 'medium') {
        $stmt = $this->pdo->prepare("INSERT INTO tickets (admin_id, entreprise_id, subject, priority, status) VALUES (?, ?, ?, ?, 'open')");
        if ($stmt->execute([$admin_id, $entreprise_id, $subject, $priority])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function addMessage($ticket_id, $sender_id, $sender_role, $message, $attachment_path = null) {
        $stmt = $this->pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, sender_role, message, attachment_path) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$ticket_id, $sender_id, $sender_role, $message, $attachment_path]);
    }

    public function getByAdmin($admin_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE admin_id = ? ORDER BY created_at DESC");
        $stmt->execute([$admin_id]);
        return $stmt->fetchAll();
    }

    public function getAllForSupport() {
        $stmt = $this->pdo->query("SELECT t.*, e.nom as entreprise_nom FROM tickets t JOIN entreprises e ON t.entreprise_id = e.id ORDER BY t.created_at DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT t.*, e.nom as entreprise_nom FROM tickets t JOIN entreprises e ON t.entreprise_id = e.id WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getMessages($ticket_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC");
        $stmt->execute([$ticket_id]);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function countUnreadForAdmin($admin_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ticket_messages tm JOIN tickets t ON tm.ticket_id = t.id WHERE t.admin_id = ? AND tm.sender_role = 'superadmin' AND tm.is_read = 0");
        $stmt->execute([$admin_id]);
        return $stmt->fetchColumn();
    }

    public function countUnreadForSupport() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'");
        return $stmt->fetchColumn();
    }

    public function markAsRead($ticket_id, $role_to_mark) {
        $stmt = $this->pdo->prepare("UPDATE ticket_messages SET is_read = 1 WHERE ticket_id = ? AND sender_role = ?");
        return $stmt->execute([$ticket_id, $role_to_mark]);
    }
}
