// Copyright 2019. DW </> Web-Engineering. All rights reserved.
import ReactDOM from "react-dom";
import React from "react";
import LayoutSidebarAside from "./LayoutSidebarAside";


let _layout_sidebar_aside_react_entry = document.getElementById('_layout_sidebar_aside_react_entry');

if(_layout_sidebar_aside_react_entry){
    ReactDOM.render(<LayoutSidebarAside />, _layout_sidebar_aside_react_entry);
}