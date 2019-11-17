import React from 'react';
import {NavLink, withRouter} from 'react-router-dom';

const NavItem = withRouter(props => {
    const { to, children, location } = props;
    return (
        <li className={location.pathname === to ? "active" : null}>
            <NavLink to={to}>{children}</NavLink>
        </li>
    );
});

export default NavItem;
