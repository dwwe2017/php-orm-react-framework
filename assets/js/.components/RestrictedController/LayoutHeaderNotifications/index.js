// Copyright 2019. DW </> Web-Engineering. All rights reserved.
import React, {Component} from "react";

export default class LayoutHeaderNotifications extends Component {
    constructor(props){
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
        const messages = this.state.messages < 1000 ? (this.state.messages+1) : 1000;
        this.setState({
            messages
        });
    }

    render() {
        return (
            <ul className="nav navbar-nav navbar-right" >
                <li className="dropdown" >
                    <a href="#" className="dropdown-toggle" data-toggle="dropdown" >
                        <i className="icon-warning-sign" />
                        <span className="badge" >{this.state.messages.toString()}</span >
                    </a >
                    <ul className="dropdown-menu extended notification" >
                        <li className="title" >
                            <p >You have {this.state.messages.toString()} new notifications</p >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="label label-success" ><i className="icon-plus" /></span >
                                <span className="message" >New user registration.</span >
                                <span className="time" >1 mins</span >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="label label-danger" ><i className="icon-warning-sign" /></span >
                                <span className="message" >High CPU load on cluster #2.</span >
                                <span className="time" >5 mins</span >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="label label-success" ><i className="icon-plus" /></span >
                                <span className="message" >New user registration.</span >
                                <span className="time" >10 mins</span >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="label label-info" ><i className="icon-bullhorn" /></span >
                                <span className="message" >New items are in queue.</span >
                                <span className="time" >25 mins</span >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="label label-warning" ><i className="icon-bolt" /></span >
                                <span className="message" >Disk space to 85% full.</span >
                                <span className="time" >55 mins</span >
                            </a >
                        </li >
                        <li className="footer" >
                            <a href="javascript:void(0);" >View all notifications</a >
                        </li >
                    </ul >
                </li >
            </ul >
        )
    }
}
