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

namespace Controllers;


use Exceptions\MethodNotFoundException;
use Interfaces\ControllerInterfaces\InvokeControllerInterface;

/**
 * Class InvokeController
 * @package Controllers
 */
class PublicInvokeController extends PublicController implements InvokeControllerInterface
{
    /**
     * @return mixed|null
     */
    public function indexAction()
    {
        return null;
    }

    /**
     * @param string $action
     * @return mixed
     * @throws MethodNotFoundException
     */
    public final function run(string $action)
    {
        $methodName = $action . 'Action';
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            throw new MethodNotFoundException(sprintf("Method %s in class %s was not found or could not be loaded", $methodName, get_class($this)));
        }
    }

    /**
     *
     */
    public final function signOutAction(): void
    {
        $this->getSessionHandler()->signOut();
        $this->redirect(null, "publicFront", "login");
    }
}
