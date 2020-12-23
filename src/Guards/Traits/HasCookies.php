<?php

namespace Zauth\Guards\Traits;

use RuntimeException;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Symfony\Component\HttpFoundation\Cookie;

trait HasCookies
{
    /**
     * The Illuminate cookie creator service.
     *
     * @var \Illuminate\Contracts\Cookie\QueueingFactory
     */
    protected $cookie;

    /**
     * Set the recaller cookie for response.
     *
     * @param string $name
     * @param string $value
     */
    public function queueRecallerCookie($name, $value)
    {
        $this->getCookieJar()->queue(
            $this->getCookieJar()->forever($name, $value)
        );
    }

    /**
     * Set the cookie for response.
     *
     * @param string $name
     * @param string $value
     * @param int $expiry
     */
    public function queueCookie($name, $value, $expiry)
    {
        $this->getCookieJar()->queue(
            $this->getCookieJar()->make($name, $value, $expiry)
        );
    }

    /**
     * Get the cookie creator instance used by the guard.
     *
     * @return \Illuminate\Contracts\Cookie\QueueingFactory
     *
     * @throws \RuntimeException
     */
    public function getCookieJar()
    {
        if (!isset($this->cookie)) {
            throw new RuntimeException('Cookie jar has not been set.');
        }

        return $this->cookie;
    }

    /**
     * Set the cookie creator instance used by the guard.
     *
     * @param  \Illuminate\Contracts\Cookie\QueueingFactory $cookie
     * @return void
     */
    public function setCookieJar(CookieJar $cookie)
    {
        $this->cookie = $cookie;
    }
}
