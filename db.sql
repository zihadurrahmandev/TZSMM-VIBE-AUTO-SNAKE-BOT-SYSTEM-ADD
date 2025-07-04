INSERT INTO `crazydeveloperbd_settings` (`id`, `key`, `type`, `category`, `value`, `created_at`, `updated_at`) 
VALUES 
  (NULL, 'snake_bot_status', 'text', 'Snake Settings', 'on', NULL, '2025-06-29 22:58:11'),
  (NULL, 'snake_bot_winning', 'number', 'Snake Settings', '70', NULL, '2025-06-29 22:19:04');

ALTER TABLE games
    ADD game_type VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'Player' AFTER game,
    ADD is_bot_winner VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER game_type,
    MODIFY player_2_url VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    MODIFY player_2_api_id VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    MODIFY winner_id BIGINT UNSIGNED NULL,
    MODIFY player_2 BIGINT UNSIGNED NULL;
