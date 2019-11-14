// Copyright 2019. DW </> Web-Engineering. All rights reserved.

import React from "react";
import {Link, Route, Switch} from "react-router-dom";
import {Div} from "tsi2-ui-library";
import Overview from "./Overview";
import EditAccount from "./EditAccount";

const Routes = ({ ...props }) => (
    <Div cols={"12"}>
        <div className="tabbable tabbable-custom tabbable-full-width" >
            <ul className="nav nav-tabs" >
                <li >
                    <a href="#tab_overview" data-toggle="tab" >
                        <Link  to="/">Overview</Link>
                    </a >
                </li >
                <li >
                    <a href="#tab_edit_account" data-toggle="tab" >
                        <Link to="/edit_account">Edit Account</Link>
                    </a >
                </li >
            </ul >
            <div className="tab-content" >
                <Switch>
                    <Route exact={true} path="/" render={(routeProps) => <Overview route={routeProps} {...props} />} />
                    <Route path="/edit_account" render={(routeProps) => <EditAccount route={routeProps} {...props} />} />
                </Switch>
            </div>
        </div>
    </Div>
);

export default Routes;
