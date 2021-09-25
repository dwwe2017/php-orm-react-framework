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

import React, {Component} from "react";

export default class LayoutHeaderNotifications extends Component {
    constructor(props) {
        super(props);
        this.state = {
            messages: 0
        }
    }

    componentDidMount() {
        this.timerID = setInterval(
            () => this.tick(),
            150
        );
    }

    componentWillUnmount() {
        clearInterval(this.timerID);
    }

    tick() {
        const messages = this.state.messages < 1000 ? (this.state.messages + 1) : 1000;
        this.setState({
            messages
        });
    }

    render() {
        return (
            <li className="c-header-nav-item dropdown d-md-down-none mx-2"><a className="c-header-nav-link"
                                                                              data-toggle="dropdown" href="#"
                                                                              role="button" aria-haspopup="true"
                                                                              aria-expanded="false">
                <i className="c-icon cil-bell"> </i>
                <span className="badge badge-pill badge-danger">{this.state.messages.toString()}</span></a>
                <div className="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
                    <div className="dropdown-header bg-light"><strong>You
                        have {this.state.messages.toString()} notifications</strong></div>
                    <a className="dropdown-item" href="#">
                        <i className="c-icon mfe-2 text-success cil-user-follow"> </i>
                        New user registered</a><a className="dropdown-item" href="#">
                    <i className="c-icon mfe-2 text-danger cil-user-unfollow"> </i>
                    User deleted</a><a className="dropdown-item" href="#">
                    <i className="c-icon mfe-2 text-info cil-chart"> </i>
                    Sales report is ready</a><a className="dropdown-item" href="#">
                    <i className="c-icon mfe-2 text-success cil-basket"> </i>
                    New client</a><a className="dropdown-item" href="#">
                    <i className="c-icon mfe-2 text-warning cil-speedometer"> </i>
                    Server overloaded</a>
                    <div className="dropdown-header bg-light"><strong>Server</strong></div>
                    <a className="dropdown-item d-block" href="#">
                        <div className="text-uppercase mb-1"><small><b>CPU Usage</b></small></div>
                        <span className="progress progress-xs">
                  <div className="progress-bar bg-info" role="progressbar" style={{width: 25 + "%"}} aria-valuenow="25"
                       aria-valuemin="0" aria-valuemax="100"/>
                </span><small className="text-muted">348 Processes. 1/4 Cores.</small>
                    </a><a className="dropdown-item d-block" href="#">
                    <div className="text-uppercase mb-1"><small><b>Memory Usage</b></small></div>
                    <span className="progress progress-xs">
                  <div className="progress-bar bg-warning" role="progressbar" style={{width: 70 + "%"}}
                       aria-valuenow="70"
                       aria-valuemin="0" aria-valuemax="100"/>
                </span><small className="text-muted">11444GB/16384MB</small>
                </a><a className="dropdown-item d-block" href="#">
                    <div className="text-uppercase mb-1"><small><b>SSD 1 Usage</b></small></div>
                    <span className="progress progress-xs">
                  <div className="progress-bar bg-danger" role="progressbar" style={{width: 95 + "%"}}
                       aria-valuenow="95"
                       aria-valuemin="0" aria-valuemax="100"/>
                </span><small className="text-muted">243GB/256GB</small>
                </a>
                </div>
            </li>
        )
    }
}
