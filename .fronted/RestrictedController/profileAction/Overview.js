import React from 'react';
import {Div, WidgetBox, WidgetContent, WidgetHeader} from "tsi2-ui-library";

export default class Overview extends React.Component {
    constructor(props) {
        super(props);
        console.log(props);
        this.state = {date: new Date()};
    }

    componentDidMount() {
        this.timerID = setInterval(
            () => this.tick(),
            1000
        );
    }

    componentWillUnmount() {
        clearInterval(this.timerID);
    }

    tick() {
        this.setState({
            date: new Date()
        });
    }

    render() {
        const timeText = <span >It's {this.state.date.toLocaleTimeString()}</span >;

        return (
            <Div cols={"12"} >
                <WidgetBox >
                    <WidgetHeader title={"Time Example"} />
                    <WidgetContent padding={true} >
                        <div className="tabbable box-tabs" >
                            <ul className="nav nav-tabs" >
                                <li className="" ><a href="#box_tab3" data-toggle="tab" >Section 3</a ></li >
                                <li className="" ><a href="#box_tab2" data-toggle="tab" >Section 2</a ></li >
                                <li className="active" ><a href="#box_tab1" data-toggle="tab" >Time is running..</a ></li >
                            </ul >
                            <div className="tab-content" >
                                <div className="tab-pane active" id="box_tab1" >
                                    <div className="alert alert-warning" ><strong >Hey there!</strong > I'm a cool
                                        alert.
                                    </div >
                                    <p >
                                        {timeText}
                                    </p >
                                </div >
                                <div className="tab-pane" id="box_tab2" >
                                    <p >Content #2</p >
                                </div >
                                <div className="tab-pane" id="box_tab3" >
                                    <p >Content #3</p >
                                </div >
                            </div >
                        </div >
                    </WidgetContent >
                </WidgetBox >
            </Div >
        );
    }
}
