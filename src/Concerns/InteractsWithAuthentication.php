<?php

namespace Rampall\Dusk\Concerns;

use Rampall\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

trait InteractsWithAuthentication
{
    /**
     * Log into the application as the default user.
     *
     * @return $this
     */
    public function login()
    {
        return $this->loginAs(call_user_func(Browser::$userResolver));
    }

    /**
     * Log into the application using a given user ID or email.
     *
     * @param  object|string  $userId
     * @param  string  $guard
     * @return $this
     */
    public function loginAs($userId, $guard = null)
    {
        $userId = method_exists($userId, 'getKey') ? $userId->getKey() : $userId;

        return $this->visit(rtrim(route('dusk.login', ['userId' => $userId, 'guard' => $guard], $this->shouldUseAbsoluteRouteForAuthentication())));
    }

    /**
     * Log out of the application.
     *
     * @param  string  $guard
     * @return $this
     */
    public function logout($guard = null)
    {
        return $this->visit(rtrim(route('dusk.logout', ['guard' => $guard], $this->shouldUseAbsoluteRouteForAuthentication()), '/'));
    }

    /**
     * Get the ID and the class name of the authenticated user.
     *
     * @param  string|null  $guard
     * @return array
     */
    protected function currentUserInfo($guard = null)
    {
        $response = $this->visit(route('dusk.user', ['guard' => $guard], $this->shouldUseAbsoluteRouteForAuthentication()));

        return json_decode(strip_tags($response->driver->getPageSource()), true);
    }

    /**
     * Assert that the user is authenticated.
     *
     * @param  string|null  $guard
     * @return $this
     */
    public function assertAuthenticated($guard = null)
    {
        PHPUnit::assertNotEmpty($this->currentUserInfo($guard), 'The user is not authenticated.');

        return $this;
    }

    /**
     * Assert that the user is not authenticated.
     *
     * @param  string|null  $guard
     * @return $this
     */
    public function assertGuest($guard = null)
    {
        PHPUnit::assertEmpty(
            $this->currentUserInfo($guard), 'The user is unexpectedly authenticated.'
        );

        return $this;
    }

    /**
     * Assert that the user is authenticated as the given user.
     *
     * @param  mixed  $user
     * @param  string|null  $guard
     * @return $this
     */
    public function assertAuthenticatedAs($user, $guard = null)
    {
        $expected = [
            'id' => $user->getAuthIdentifier(),
            'className' => get_class($user),
        ];

        PHPUnit::assertSame(
            $expected, $this->currentUserInfo($guard),
            'The currently authenticated user is not who was expected.'
        );

        return $this;
    }

    /**
     * Determine if route() should use an absolute path.
     *
     * @return bool
     */
    private function shouldUseAbsoluteRouteForAuthentication()
    {
        return config('dusk.domain') !== null;
    }
}
