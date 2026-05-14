<?php
/**
 * EventManager - Système de Hooks pour RHXtimes
 * Permet d'ajouter des fonctionnalités (WhatsApp, Email, Logs) sans modifier le cœur de l'application.
 */
class EventManager {
    private static $listeners = [];

    /**
     * Enregistre un écouteur pour un événement
     */
    public static function on($event, $callback) {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        self::$listeners[$event][] = $callback;
    }

    /**
     * Déclenche un événement
     * @param string $event Nom de l'événement (ex: 'pointage.created')
     * @param mixed $data Données à passer aux écouteurs
     */
    public static function trigger($event, $data = null) {
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $callback) {
                try {
                    call_user_func($callback, $data);
                } catch (Exception $e) {
                    error_log("Erreur Hook [$event] : " . $e->getMessage());
                }
            }
        }
    }
}
