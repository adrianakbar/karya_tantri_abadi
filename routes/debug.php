Route::get('/debug-permissions', function () {
    $user = auth()->user();
    if (!$user) {
        return 'Not logged in';
    }

    $debug = [
        'user' => $user->only(['id', 'name', 'email']),
        'roles' => $user->roles->map(fn($role) => [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')
        ])
    ];

    return response()->json($debug);
});
