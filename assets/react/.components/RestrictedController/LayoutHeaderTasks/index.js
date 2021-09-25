/*
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

import React, {Component, Fragment} from "react";

export default class LayoutHeaderTasks extends Component {
    constructor(props) {
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
        const progress1 = this.state.progress1 < 100 ? (this.state.progress1 + 1) : 100;
        this.setState({
            progress1
        });
    }

    tick2() {
        const progress2 = this.state.progress2 < 100 ? (this.state.progress2 + 1) : 100;
        this.setState({
            progress2
        });
    }

    tick3() {
        const progress3 = this.state.progress3 < 100 ? (this.state.progress3 + 1) : 100;
        this.setState({
            progress3
        });
    }

    tick4() {
        const progress4 = this.state.progress4 < 100 ? (this.state.progress4 + 1) : 100;
        this.setState({
            progress4
        });
    }

    render() {
        return (
            <li className="c-header-nav-item dropdown d-md-down-none mx-2"><a className="c-header-nav-link"
                                                                              data-toggle="dropdown" href="#"
                                                                              role="button" aria-haspopup="true"
                                                                              aria-expanded="false">
                <i className="c-icon cil-list-rich"> </i>
                <span className="badge badge-pill badge-warning">15</span></a>
                <div className="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
                    <div className="dropdown-header bg-light"><strong>You have 5 pending tasks</strong></div>
                    <a className="dropdown-item d-block" href="#">
                        <div className="small mb-1">Upgrade NPM &amp; Bower<span
                            className="float-right"><strong>{this.state.progress1.toString()}%</strong></span></div>
                        <span className="progress progress-xs">
                  <div className="progress-bar bg-info" role="progressbar"
                       style={{width: this.state.progress1.toString() + "%"}}
                       aria-valuemin="0" aria-valuemax="100"/>
                </span>
                    </a><a className="dropdown-item d-block" href="#">
                    <div className="small mb-1">ReactJS Version<span
                        className="float-right"><strong>{this.state.progress2.toString()}%</strong></span>
                    </div>
                    <span className="progress progress-xs">
                  <div className="progress-bar bg-danger" role="progressbar"
                       style={{width: this.state.progress2.toString() + "%"}}
                       aria-valuemin="0" aria-valuemax="100"/>
                </span>
                </a><a className="dropdown-item d-block" href="#">
                    <div className="small mb-1">VueJS Version<span
                        className="float-right"><strong>{this.state.progress3.toString()}%</strong></span>
                    </div>
                    <span className="progress progress-xs">
                  <div className="progress-bar bg-warning" role="progressbar"
                       style={{width: this.state.progress3.toString() + "%"}}
                       aria-valuemin="0" aria-valuemax="100"/>
                </span>
                </a><a className="dropdown-item d-block" href="#">
                    <div className="small mb-1">Add new layouts<span
                        className="float-right"><strong>{this.state.progress4.toString()}%</strong></span>
                    </div>
                    <span className="progress progress-xs">
                  <div className="progress-bar bg-info" role="progressbar"
                       style={{width: this.state.progress4.toString() + "%"}}
                       aria-valuemin="0" aria-valuemax="100"/>
                </span>
                </a><a className="dropdown-item d-block" href="#">
                    <div className="small mb-1">Angular 8 Version<span
                        className="float-right"><strong>100%</strong></span></div>
                    <span className="progress progress-xs">
                  <div className="progress-bar bg-success" role="progressbar" style={{width: 100 + "%"}}
                       aria-valuemin="0" aria-valuemax="100"/>
                </span>
                </a><a className="dropdown-item text-center border-top" href="#"><strong>View all tasks</strong></a>
                </div>
            </li>
        )
    }
}
