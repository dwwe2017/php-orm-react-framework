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

import React, {Component, Fragment} from "react";

export default class LayoutSidebarAside extends Component {
    render() {
        return (
            <div className="c-sidebar c-sidebar-lg c-sidebar-light c-sidebar-right c-sidebar-overlaid" id="aside">
                <button className="c-sidebar-close c-class-toggler" type="button" data-target="_parent"
                        data-class="c-sidebar-show" responsive="true">
                    <svg className="c-icon">
                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-x"/>
                    </svg>
                </button>
                <ul className="nav nav-tabs nav-underline nav-underline-primary" role="tablist">
                    <li className="nav-item"><a className="nav-link active" data-toggle="tab" href="#timeline"
                                                role="tab">
                        <svg className="c-icon">
                            <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-list"/>
                        </svg>
                    </a></li>
                    <li className="nav-item"><a className="nav-link" data-toggle="tab" href="#messages" role="tab">
                        <svg className="c-icon">
                            <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-speech"/>
                        </svg>
                    </a></li>
                    <li className="nav-item"><a className="nav-link" data-toggle="tab" href="#settings" role="tab">
                        <svg className="c-icon">
                            <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-settings"/>
                        </svg>
                    </a></li>
                </ul>
                <div className="tab-content">
                    <div className="tab-pane active" id="timeline" role="tabpanel">
                        <div className="list-group list-group-accent">
                            <div
                                className="list-group-item list-group-item-accent-secondary bg-light text-center font-weight-bold text-muted text-uppercase c-small">Today
                            </div>
                            <div className="list-group-item list-group-item-accent-warning list-group-item-divider">
                                <div className="c-avatar float-right"><img className="c-avatar-img"
                                                                           src="assets/img/avatars/7.jpg"
                                                                           alt="user@email.com"/></div>
                                <div>Meeting with <strong>Lucas</strong></div>
                                <small className="text-muted mr-3">
                                    <svg className="c-icon">
                                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-calendar"/>
                                    </svg>
                                    &nbsp; 1 - 3pm</small><small className="text-muted">
                                <svg className="c-icon">
                                    <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-location-pin"/>
                                </svg>
                                &nbsp; Palo Alto, CA</small>
                            </div>
                            <div className="list-group-item list-group-item-accent-info">
                                <div className="c-avatar float-right"><img className="c-avatar-img"
                                                                           src="assets/img/avatars/4.jpg"
                                                                           alt="user@email.com"/></div>
                                <div>Skype with <strong>Megan</strong></div>
                                <small className="text-muted mr-3">
                                    <svg className="c-icon">
                                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-calendar"/>
                                    </svg>
                                    &nbsp; 4 - 5pm</small><small className="text-muted">
                                <svg className="c-icon">
                                    <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-skype"/>
                                </svg>
                                &nbsp; On-line</small>
                            </div>
                            <div
                                className="list-group-item list-group-item-accent-secondary bg-light text-center font-weight-bold text-muted text-uppercase c-small">Tomorrow
                            </div>
                            <div className="list-group-item list-group-item-accent-danger list-group-item-divider">
                                <div>New UI Project - <strong>deadline</strong></div>
                                <small className="text-muted mr-3">
                                    <svg className="c-icon">
                                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-calendar"/>
                                    </svg>
                                    &nbsp; 10 - 11pm</small><small className="text-muted">
                                <svg className="c-icon">
                                    <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-home"/>
                                </svg>
                                &nbsp; creativeLabs HQ</small>
                                <div className="c-avatars-stack mt-2">
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/2.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/3.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/4.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/5.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/6.jpg"
                                                                               alt="user@email.com"/></div>
                                </div>
                            </div>
                            <div className="list-group-item list-group-item-accent-success list-group-item-divider">
                                <div><strong>#10 Startups.Garden</strong> Meetup</div>
                                <small className="text-muted mr-3">
                                    <svg className="c-icon">
                                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-calendar"/>
                                    </svg>
                                    &nbsp; 1 - 3pm</small><small className="text-muted">
                                <svg className="c-icon">
                                    <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-location-pin"/>
                                </svg>
                                &nbsp; Palo Alto, CA</small>
                            </div>
                            <div className="list-group-item list-group-item-accent-primary list-group-item-divider">
                                <div><strong>Team meeting</strong></div>
                                <small className="text-muted mr-3">
                                    <svg className="c-icon">
                                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-calendar"/>
                                    </svg>
                                    &nbsp; 4 - 6pm</small><small className="text-muted">
                                <svg className="c-icon">
                                    <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-home"/>
                                </svg>
                                &nbsp; creativeLabs HQ</small>
                                <div className="c-avatars-stack mt-2">
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/2.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/3.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/4.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/5.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/6.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/7.jpg"
                                                                               alt="user@email.com"/></div>
                                    <div className="c-avatar c-avatar-xs"><img className="c-avatar-img"
                                                                               src="assets/img/avatars/8.jpg"
                                                                               alt="user@email.com"/></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="tab-pane p-3" id="messages" role="tabpanel">
                        <div className="message">
                            <div className="py-3 pb-5 mr-3 float-left">
                                <div className="c-avatar"><img className="c-avatar-img" src="assets/img/avatars/7.jpg"
                                                               alt="user@email.com"/><span
                                    className="c-avatar-status bg-success"/></div>
                            </div>
                            <div><small className="text-muted">Lukasz Holeczek</small><small
                                className="text-muted float-right mt-1">1:52 PM</small></div>
                            <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
                            <small className="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed
                                do eiusmod tempor incididunt...</small>
                        </div>
                        <hr/>
                        <div className="message">
                            <div className="py-3 pb-5 mr-3 float-left">
                                <div className="c-avatar"><img className="c-avatar-img"
                                                               src="assets/img/avatars/7.jpg"
                                                               alt="user@email.com"/><span
                                    className="c-avatar-status bg-success"/></div>
                            </div>
                            <div><small className="text-muted">Lukasz Holeczek</small><small
                                className="text-muted float-right mt-1">1:52 PM</small></div>
                            <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
                            <small className="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit,
                                sed do eiusmod tempor incididunt...</small>
                        </div>
                        <hr/>
                        <div className="message">
                            <div className="py-3 pb-5 mr-3 float-left">
                                <div className="c-avatar"><img className="c-avatar-img"
                                                               src="assets/img/avatars/7.jpg"
                                                               alt="user@email.com"/><span
                                    className="c-avatar-status bg-success"/></div>
                            </div>
                            <div><small className="text-muted">Lukasz Holeczek</small><small
                                className="text-muted float-right mt-1">1:52 PM</small></div>
                            <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
                            <small className="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing
                                elit, sed do eiusmod tempor incididunt...</small>
                        </div>
                        <hr/>
                        <div className="message">
                            <div className="py-3 pb-5 mr-3 float-left">
                                <div className="c-avatar"><img className="c-avatar-img"
                                                               src="assets/img/avatars/7.jpg"
                                                               alt="user@email.com"/><span
                                    className="c-avatar-status bg-success"/></div>
                            </div>
                            <div><small className="text-muted">Lukasz Holeczek</small><small
                                className="text-muted float-right mt-1">1:52 PM</small></div>
                            <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
                            <small className="text-muted">Lorem ipsum dolor sit amet, consectetur
                                adipisicing elit, sed do eiusmod tempor incididunt...</small>
                        </div>
                        <hr/>
                        <div className="message">
                            <div className="py-3 pb-5 mr-3 float-left">
                                <div className="c-avatar"><img className="c-avatar-img"
                                                               src="assets/img/avatars/7.jpg"
                                                               alt="user@email.com"/><span
                                    className="c-avatar-status bg-success"/></div>
                            </div>
                            <div><small className="text-muted">Lukasz Holeczek</small><small
                                className="text-muted float-right mt-1">1:52 PM</small></div>
                            <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet
                            </div>
                            <small className="text-muted">Lorem ipsum dolor sit amet, consectetur
                                adipisicing elit, sed do eiusmod tempor incididunt...</small>
                        </div>
                    </div>
                    <div className="tab-pane p-3" id="settings" role="tabpanel">
                        <h6>Settings</h6>
                        <div className="c-aside-options">
                            <div className="clearfix mt-4"><small><b>Option 1</b></small>
                                <label
                                    className="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
                                    <input className="c-switch-input" type="checkbox" checked=""/><span
                                    className="c-switch-slider" data-checked="On" data-unchecked="Off"/>
                                </label>
                            </div>
                            <div><small className="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit,
                                sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small></div>
                        </div>
                        <div className="c-aside-options">
                            <div className="clearfix mt-3"><small><b>Option 2</b></small>
                                <label
                                    className="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
                                    <input className="c-switch-input" type="checkbox"/><span className="c-switch-slider"
                                                                                             data-checked="On"
                                                                                             data-unchecked="Off"/>
                                </label>
                            </div>
                            <div><small className="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit,
                                sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small></div>
                        </div>
                        <div className="c-aside-options">
                            <div className="clearfix mt-3"><small><b>Option 3</b></small>
                                <label
                                    className="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
                                    <input className="c-switch-input" type="checkbox"/><span className="c-switch-slider"
                                                                                             data-checked="On"
                                                                                             data-unchecked="Off"/>
                                </label>
                            </div>
                        </div>
                        <div className="c-aside-options">
                            <div className="clearfix mt-3"><small><b>Option 4</b></small>
                                <label
                                    className="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
                                    <input className="c-switch-input" type="checkbox" checked=""/><span
                                    className="c-switch-slider" data-checked="On" data-unchecked="Off"/>
                                </label>
                            </div>
                        </div>
                        <hr/>
                        <h6>System Utilization</h6>
                        <div className="text-uppercase mb-1 mt-4"><small><b>CPU Usage</b></small></div>
                        <div className="progress progress-xs">
                            <div className="progress-bar bg-info" role="progressbar" style={{width: "25%"}}
                                 aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"/>
                        </div>
                        <small className="text-muted">348 Processes. 1/4 Cores.</small>
                        <div className="text-uppercase mb-1 mt-2"><small><b>Memory Usage</b></small></div>
                        <div className="progress progress-xs">
                            <div className="progress-bar bg-warning" role="progressbar" style={{width: "70%"}}
                                 aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"/>
                        </div>
                        <small className="text-muted">11444GB/16384MB</small>
                        <div className="text-uppercase mb-1 mt-2"><small><b>SSD 1 Usage</b></small></div>
                        <div className="progress progress-xs">
                            <div className="progress-bar bg-danger" role="progressbar" style={{width: "95%"}}
                                 aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"/>
                        </div>
                        <small className="text-muted">243GB/256GB</small>
                        <div className="text-uppercase mb-1 mt-2"><small><b>SSD 2 Usage</b></small></div>
                        <div className="progress progress-xs">
                            <div className="progress-bar bg-success" role="progressbar" style={{width: "10%"}}
                                 aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"/>
                        </div>
                        <small className="text-muted">25GB/256GB</small>
                    </div>
                </div>
            </div>
        )
    }
}
