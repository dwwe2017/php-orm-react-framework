// Copyright 2019. DW </> Web-Engineering. All rights reserved.
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
                <svg className="c-icon">
                    <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-bell"/>
                </svg>
                <span className="badge badge-pill badge-danger">{this.state.messages.toString()}</span></a>
                <div className="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
                    <div className="dropdown-header bg-light"><strong>You
                        have {this.state.messages.toString()} notifications</strong></div>
                    <a className="dropdown-item" href="#">
                        <svg className="c-icon mfe-2 text-success">
                            <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-user-follow"/>
                        </svg>
                        New user registered</a><a className="dropdown-item" href="#">
                    <svg className="c-icon mfe-2 text-danger">
                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-user-unfollow"/>
                    </svg>
                    User deleted</a><a className="dropdown-item" href="#">
                    <svg className="c-icon mfe-2 text-info">
                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-chart"/>
                    </svg>
                    Sales report is ready</a><a className="dropdown-item" href="#">
                    <svg className="c-icon mfe-2 text-success">
                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-basket"/>
                    </svg>
                    New client</a><a className="dropdown-item" href="#">
                    <svg className="c-icon mfe-2 text-warning">
                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-speedometer"/>
                    </svg>
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
