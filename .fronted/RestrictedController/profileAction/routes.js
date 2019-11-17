// Copyright 2019. DW </> Web-Engineering. All rights reserved.

import React, {Fragment} from "react";
import {NavLink, Route, Switch, withRouter} from "react-router-dom";
import {Div} from "tsi2-ui-library";
import Overview from "./Overview";
import EditAccount from "./EditAccount";
import NavItem from "./NavItem";

const Routes = ({ ...props }) => (
    <Div cols={"12"}>
        <div className="tabbable tabbable-custom tabbable-full-width" >
            <ul className="nav nav-tabs" >
                <NavItem to={"/"}>
                    <Fragment>Overview</Fragment>
                </NavItem>
                <NavItem to={"/edit_account"}>
                    <Fragment>Edit Account</Fragment>
                </NavItem>
            </ul >
            <div className="tab-content row" >
                <Switch>
                    <Route exact={true} path="/" render={(routeProps) => <Overview route={routeProps} {...props} />} />
                    <Route path="/edit_account" render={(routeProps) => <EditAccount route={routeProps} {...props} />} />
                </Switch>
            </div>
        </div>
    </Div>
);

export default withRouter(Routes);
