/*
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

import ReactDOM from "react-dom";
import React from "react";
import LayoutHeaderTasks from "./LayoutHeaderTasks";
import LayoutHeaderNotifications from "./LayoutHeaderNotifications";
import LayoutHeaderMessages from "./LayoutHeaderMessages";


/**
 * The following DOM element is the task menu in the header area of the UI
 * @type {HTMLElement}
 * @private
 */
let _layout_header_tasks_react_entry = document.getElementById('_layout_header_tasks_react_entry');

/**
 * Render task menu
 * @see assets/react/.components/RestrictedController/LayoutHeaderTasks
 */
if (_layout_header_tasks_react_entry) {
    ReactDOM.render(<LayoutHeaderTasks/>, document.getElementById('_layout_header_tasks_react_entry'));
}

/**
 * The following DOM element is the notification menu in the header area of the UI
 * @type {HTMLElement}
 * @private
 */
let _layout_header_notifications_react_entry = document.getElementById('_layout_header_notifications_react_entry');

/**
 * Render notifications menu
 * @see assets/react/.components/RestrictedController/LayoutHeaderNotifications
 */
if (_layout_header_notifications_react_entry) {
    ReactDOM.render(<LayoutHeaderNotifications/>, document.getElementById('_layout_header_notifications_react_entry'));
}

/**
 * The following DOM element is the messages menu in the header area of the UI
 * @type {HTMLElement}
 * @private
 */
let _layout_header_messages_react_entry = document.getElementById('_layout_header_messages_react_entry');

/**
 * Render messages menu
 * @see assets/react/.components/RestrictedController/LayoutHeaderMessages
 */
if (_layout_header_messages_react_entry) {
    ReactDOM.render(<LayoutHeaderMessages/>, document.getElementById('_layout_header_messages_react_entry'));
}