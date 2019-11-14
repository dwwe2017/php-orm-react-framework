// Copyright 2019. DW </> Web-Engineering. All rights reserved.
import React, {Component} from "react";

export default class LayoutHeaderTasks extends Component {
    constructor(props){
        super(props);
        this.state = {
            progress1: 0,
            progress2: 10,
            progress3: 20,
            progress4: 30,
        };
    }

    componentDidMount() {
        this.timerID1 = setInterval(
            () => this.tick1(),
            100
        );
        this.timerID2 = setInterval(
            () => this.tick2(),
            500
        );
        this.timerID3 = setInterval(
            () => this.tick3(),
            1000
        );
        this.timerID4 = setInterval(
            () => this.tick4(),
            2000
        );
    }

    componentWillUnmount() {
        clearInterval(this.timerID1);
        clearInterval(this.timerID2);
        clearInterval(this.timerID3);
        clearInterval(this.timerID4);
    }

    tick1() {
        const progress1 = this.state.progress1 < 100 ? (this.state.progress1+1) : 100;
        this.setState({
            progress1
        });
    }

    tick2() {
        const progress2 = this.state.progress2 < 100 ? (this.state.progress2+1) : 100;
        this.setState({
            progress2
        });
    }

    tick3() {
        const progress3 = this.state.progress3 < 100 ? (this.state.progress3+1) : 100;
        this.setState({
            progress3
        });
    }

    tick4() {
        const progress4 = this.state.progress4 < 100 ? (this.state.progress4+1) : 100;
        this.setState({
            progress4
        });
    }

    render() {
        return (
            <ul className="nav navbar-nav navbar-right" >
                <li className="dropdown hidden-xs hidden-sm" >
                    <a href="#" className="dropdown-toggle" data-toggle="dropdown" >
                        <i className="icon-tasks" />
                        <span className="badge" >4</span >
                    </a >
                    <ul className="dropdown-menu extended notification" >
                        <li className="title" >
                            <p >You have 4 pending tasks</p >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
								<span className="task" >
									<span className="desc" >Preparing new release</span >
									<span className="percent" >{this.state.progress1.toString()}%</span >
								</span >
                                <div className="progress progress-small" >
                                    <div style={{width: this.state.progress1.toString() + "%"}} className="progress-bar progress-bar-info" />
                                </div >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
								<span className="task" >
									<span className="desc" >Change management</span >
									<span className="percent" >{this.state.progress2.toString()}%</span >
								</span >
                                <div className="progress progress-small progress-striped active" >
                                    <div style={{width: this.state.progress2.toString() + "%"}} className="progress-bar progress-bar-danger" />
                                </div >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
								<span className="task" >
									<span className="desc" >Mobile development</span >
									<span className="percent" >{this.state.progress3.toString()}%</span >
								</span >
                                <div className="progress progress-small" >
                                    <div style={{width: this.state.progress3.toString() + "%"}} className="progress-bar progress-bar-success" />
                                </div >
                            </a >
                        </li >
                        <li >
                            <a href="javascript:void(0);" >
								<span className="task" >
									<span className="desc" >Database migration</span >
									<span className="percent" >{this.state.progress4.toString()}%</span >
								</span >
                                <div className="progress progress-small" >
                                    <div style={{width: this.state.progress4.toString() + "%"}} className="progress-bar progress-bar-warning" />
                                </div >
                            </a >
                        </li >
                        <li className="footer" >
                            <a href="javascript:void(0);" >View all tasks</a >
                        </li >
                    </ul >
                </li >
            </ul >
        )
    }
}
