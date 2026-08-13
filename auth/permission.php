<?php

class Permission
{
    private static bool   $initialised = false;
    private static ?array $cache       = null;

    public static function init(): void
    {
        if (self::$initialised) return;
        self::$initialised = true;
    }

    public static function isSuperAdmin(): bool
    {
        return ($_SESSION['admin_role'] ?? '') === 'super_admin';
    }

    public static function can(string $module, string $action = 'view'): bool
    {
        if (self::isSuperAdmin()) return true;
        self::load();
        $perm = self::$cache['dispatcher:' . $module] ?? null;
        if (!$perm) return false;
        return match ($action) {
            'view'   => (bool)($perm['can_view']   ?? false),
            'create' => (bool)($perm['can_create'] ?? false),
            'edit'   => (bool)($perm['can_edit']   ?? false),
            'delete' => (bool)($perm['can_delete'] ?? false),
            default  => false,
        };
    }

    public static function requireCan(string $module, string $action = 'view'): void
    {
        if (!self::can($module, $action)) {
            self::deny("You do not have permission to {$action} {$module}.");
        }
    }

    private static function load(): void
    {
        if (self::$cache !== null) return;

        $uid = $_SESSION['admin_id'] ?? null;
        if (!$uid) {
            self::$cache = [];
            return;
        }

        try {
            $db   = new SupabaseDB(null, true);
            $rows = $db->findData('user_module_permissions', [
                'user_id'     => $uid,
                'application' => 'dispatcher',
            ]);
            $cache = [];
            foreach ($rows as $row) {
                $cache['dispatcher:' . $row['module_key']] = $row;
            }
            self::$cache = $cache;
        } catch (Throwable) {
            self::$cache = [];
        }
    }

    private static function deny(string $msg): never
    {
        $isApi = str_contains(
            str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''),
            '/api/'
        ) || !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        if ($isApi) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
                http_response_code(403);
            }
            echo json_encode(['success' => false, 'error' => $msg]);
        } else {
            http_response_code(403);
            header('Location: home.php?err=forbidden');
        }
        exit;
    }
}
