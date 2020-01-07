// Copyright 2019. DW </> Web-Engineering. All rights reserved.
import ReactDOM from "react-dom";
import React from "react";
import LayoutHeaderTasks from "./LayoutHeaderTasks";
import LayoutHeaderNotifications from "./LayoutHeaderNotifications";
import LayoutHeaderMessages from "./LayoutHeaderMessages";

ReactDOM.render(<LayoutHeaderTasks />, document.getElementById('_layout_header_tasks_react_entry'));
ReactDOM.render(<LayoutHeaderNotifications />, document.getElementById('_layout_header_notifications_react_entry'));
ReactDOM.render(<LayoutHeaderMessages />, document.getElementById('_layout_header_messages_react_entry'));
