// Copyright 2019. DW </> Web-Engineering. All rights reserved.
import React, {Component, Fragment} from "react";

export default class LayoutNavigationSidebarNotifications extends Component {
    render() {
        return (
            <Fragment >
                <div className="sidebar-title" >
                    <span >Notifications</span >
                </div >
                <ul className="notifications demo-slide-in" >
                    <li style={{display: "none"}} >
                        <div className="col-left" >
                            <span className="label label-danger" ><i className="icon-warning-sign" /></span >
                        </div >
                        <div className="col-right with-margin" >
                            <span className="message" >Server <strong >#512</strong > crashed.</span >
                            <span className="time" >few seconds ago</span >
                        </div >
                    </li >
                    <li style={{display: "none"}} >
                        <div className="col-left" >
                            <span className="label label-info" ><i className="icon-envelope" /></span >
                        </div >
                        <div className="col-right with-margin" >
                            <span className="message" ><strong >John</strong > sent you a message</span >
                            <span className="time" >few second ago</span >
                        </div >
                    </li >
                    <li >
                        <div className="col-left" >
                            <span className="label label-success" ><i className="icon-plus" /></span >
                        </div >
                        <div className="col-right with-margin" >
                            <span className="message" ><strong >Emma</strong >'s account was created</span >
                            <span className="time" >4 hours ago</span >
                        </div >
                    </li >
                </ul >
            </Fragment >
        )
    }
}
