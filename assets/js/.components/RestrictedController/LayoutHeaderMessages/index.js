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
            <ul className="nav navbar-nav navbar-right" >
                <li className="dropdown hidden-xs hidden-sm" >
                    <a href="#" className="dropdown-toggle" data-toggle="dropdown" >
                        <i className="icon-envelope" />
                        <span className="badge" >{this.state.messages.toString()}</span >
                    </a >
                    <ul className="dropdown-menu extended notification" >
                        <li className="title" >
                            <p >You have {this.state.messages.toString()} new messages</p >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="photo" ><img src="assets/img/demo/avatar-1.jpg" alt="" /></span >
                                <span className="subject" >
									<span className="from" >Bob Carter</span >
									<span className="time" >Just Now</span >
								</span >
                                <span className="text" >
									Consetetur sadipscing elitr...
								</span >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="photo" ><img src="assets/img/demo/avatar-2.jpg" alt="" /></span >
                                <span className="subject" >
									<span className="from" >Jane Doe</span >
									<span className="time" >45 mins</span >
								</span >
                                <span className="text" >
									Sed diam nonumy...
								</span >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
                                <span className="photo" ><img src="assets/img/demo/avatar-3.jpg" alt="" /></span >
                                <span className="subject" >
									<span className="from" >Patrick Nilson</span >
									<span className="time" >6 hours</span >
								</span >
                                <span className="text" >
									No sea takimata sanctus...
								</span >
                            </a >
                        </li >
                        <li className="footer" >
                            <a href="javascript:void(0);" >View all messages</a >
                        </li >
                    </ul >
                </li >
            </ul >
        )
    }
}
