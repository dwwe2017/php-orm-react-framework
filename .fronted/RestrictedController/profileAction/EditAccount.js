import React from 'react';
import {Div, WidgetBox, WidgetContent, WidgetHeader, WidgetUnboxed} from "tsi2-ui-library";

export default class EditAccount extends React.Component {
    render() {
        const testText = <span>This is also an example. Adjust it as you would like ;) Have fun!</span>;

        return (
            <Div cols={"12"} >
                <WidgetBox >
                    <WidgetHeader title={"Test Example"} />
                    <WidgetContent>
                        {testText}
                    </WidgetContent>
                </WidgetBox >
            </Div>
        );
    }
}
