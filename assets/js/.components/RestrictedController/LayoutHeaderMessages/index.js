// Copyright 2019. DW </> Web-Engineering. All rights reserved.
import React, {Component} from "react";

export default class LayoutHeaderMessages extends Component {
    constructor(props) {
        super(props);
        this.state = {
            messages: 0
        }
    }

    componentDidMount() {
        this.timerID = setInterval(
            () => this.tick(),
            300
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
            <li className="c-header-nav-item dropdown d-md-down-none mx-2">
                <a className="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
                   aria-expanded="false">
                    <svg className="c-icon">
                        <use xlinkHref="assets/vendors/@coreui/icons/svg/free.svg#cil-envelope-open"/>
                    </svg>
                    <span className="badge badge-pill badge-info">{this.state.messages.toString()}</span></a>
                <div className="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
                    <div className="dropdown-header bg-light"><strong>You
                        have {this.state.messages.toString()} messages</strong></div>
                    <a className="dropdown-item" href="#">
                        <div className="message">
                            <div className="py-3 mfe-3 float-left">
                                <div className="c-avatar">
                                    <img className="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"/>
                                    <span className="c-avatar-status bg-success"/>
                                </div>
                            </div>
                            <div><small className="text-muted">John Doe</small><small
                                className="text-muted float-right mt-1">Just now</small></div>
                            <div className="text-truncate font-weight-bold"><span
                                className="text-danger">!</span> Important message
                            </div>
                            <div className="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur
                                adipisicing elit, sed do eiusmod tempor incididunt...
                            </div>
                        </div>
                    </a><a className="dropdown-item" href="#">
                    <div className="message">
                        <div className="py-3 mfe-3 float-left">
                            <div className="c-avatar"><img className="c-avatar-img" src="assets/img/avatars/6.jpg"
                                                           alt="user@email.com"/><span
                                className="c-avatar-status bg-warning"/></div>
                        </div>
                        <div><small className="text-muted">John Doe</small><small
                            className="text-muted float-right mt-1">5 minutes ago</small></div>
                        <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
                        <div className="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur
                            adipisicing elit, sed do eiusmod tempor incididunt...
                        </div>
                    </div>
                </a><a className="dropdown-item" href="#">
                    <div className="message">
                        <div className="py-3 mfe-3 float-left">
                            <div className="c-avatar"><img className="c-avatar-img" src="assets/img/avatars/5.jpg"
                                                           alt="user@email.com"/><span
                                className="c-avatar-status bg-danger"/></div>
                        </div>
                        <div><small className="text-muted">John Doe</small><small
                            className="text-muted float-right mt-1">1:52 PM</small></div>
                        <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
                        <div className="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur
                            adipisicing elit, sed do eiusmod tempor incididunt...
                        </div>
                    </div>
                </a><a className="dropdown-item" href="#">
                    <div className="message">
                        <div className="py-3 mfe-3 float-left">
                            <div className="c-avatar"><img className="c-avatar-img" src="assets/img/avatars/4.jpg"
                                                           alt="user@email.com"/><span
                                className="c-avatar-status bg-info"/></div>
                        </div>
                        <div><small className="text-muted">John Doe</small><small
                            className="text-muted float-right mt-1">4:03 PM</small></div>
                        <div className="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
                        <div className="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur
                            adipisicing elit, sed do eiusmod tempor incididunt...
                        </div>
                    </div>
                </a><a className="dropdown-item text-center border-top" href="#"><strong>View all messages</strong></a>
                </div>
            </li>
        )
    }
}
