<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Traits\ControllerTraits;


use Entities\Group;
use Entities\User;

/**
 * Trait RestrictedBaseTrait
 * @package Traits\ControllerTraits
 */
trait RestrictedControllerTrait
{
    /**
     * @var bool
     */
    private bool $registered = false;

    /**
     * @var User|null
     */
    private ?User $user;

    /**
     * @var Group|null
     */
    private ?Group $group;

    /**
     * @return User|null
     */
    protected function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return Group|null
     */
    protected function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    protected function isRegistered(): bool
    {
        return $this->registered;
    }
}