<?php

declare(strict_types=1);

namespace App\Message;

/**
 * ============================================================
 * 📬 MESSAGE - Wiadomość do wysłania emaila powitalnego
 * ============================================================
 * 
 * CZYM JEST MESSAGE W SYMFONY MESSENGER?
 * 
 * Message to prosta klasa PHP zawierająca dane potrzebne do wykonania zadania.
 * Jest to "koperta" z instrukcjami, która trafia do kolejki.
 * 
 * WAŻNE:
 * - Message powinien być lekki (tylko ID, nie cały obiekt!)
 * - Message musi być serializowalny (przechowywany w Redis)
 * - Message nie zawiera logiki, tylko dane
 * 
 * JAK TO DZIAŁA:
 * 1. Controller tworzy Message i wysyła do kolejki
 * 2. Message trafia do Redis (lub innego transportu)
 * 3. Worker (osobny proces) pobiera Message z kolejki
 * 4. Worker wywołuje odpowiedni Handler
 * 5. Handler wykonuje właściwą pracę (wysyła email)
 */
final class SendWelcomeEmailMessage
{
    /**
     * Przechowujemy tylko ID, nie cały obiekt User!
     * Dlaczego? Bo:
     * 1. Obiekt może być duży
     * 2. Stan obiektu może się zmienić przed przetworzeniem
     * 3. Handler pobierze świeże dane z bazy
     */
    public function __construct(
        private int $userId
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }
}
