var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

import DataTables from "plugins/datatables/jquery.dataTables.min";

"use strict";

/**
 *
 */

var ListAction = function (_React$Component) {
    _inherits(ListAction, _React$Component);

    function ListAction(props) {
        _classCallCheck(this, ListAction);

        var _this = _possibleConstructorReturn(this, (ListAction.__proto__ || Object.getPrototypeOf(ListAction)).call(this, props));

        _this.state = {
            data: null
        };
        var baseUrl = _this.props.baseUrl;

        axios.get("index.php?module=user&controller=api&action=list").then(function (_ref) {
            var data = _ref.data;
            return _this.setState({ data: data });
        });
        return _this;
    }

    _createClass(ListAction, [{
        key: "componentDidMount",
        value: function componentDidMount() {}
    }, {
        key: "render",
        value: function render() {
            var data = this.state.data;


            if (!data) {
                return null;
            }

            return React.createElement(
                "div",
                { className: "col-md-12" },
                React.createElement(
                    "div",
                    { className: "widget box" },
                    React.createElement(
                        "div",
                        { className: "widget-header" },
                        React.createElement(
                            "h4",
                            null,
                            React.createElement("i", { className: "icon-reorder" }),
                            " Responsive Table ",
                            React.createElement(
                                "code",
                                null,
                                "table-responsive"
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "toolbar no-padding" },
                            React.createElement(
                                "div",
                                { className: "btn-group" },
                                React.createElement(
                                    "span",
                                    { className: "btn btn-xs widget-collapse" },
                                    React.createElement("i", { className: "icon-angle-down" })
                                )
                            )
                        )
                    ),
                    React.createElement(
                        "div",
                        { className: "widget-content no-padding" },
                        React.createElement(
                            "table",
                            { className: "table table-responsive datatable" },
                            React.createElement(
                                "thead",
                                null,
                                React.createElement(
                                    "tr",
                                    null,
                                    React.createElement(
                                        "th",
                                        { "data-class": "expand" },
                                        "First Name"
                                    ),
                                    React.createElement(
                                        "th",
                                        null,
                                        "Last Name"
                                    ),
                                    React.createElement(
                                        "th",
                                        { "data-hide": "phone" },
                                        "Username"
                                    ),
                                    React.createElement(
                                        "th",
                                        { "data-hide": "phone,tablet" },
                                        "Status"
                                    )
                                )
                            ),
                            React.createElement(
                                "tbody",
                                null,
                                data.data.map(function (user, i) {
                                    console.log("Entered");
                                    // Return the element. Also pass key
                                    return React.createElement(
                                        "tr",
                                        null,
                                        React.createElement(
                                            "td",
                                            null,
                                            i
                                        ),
                                        React.createElement(
                                            "td",
                                            null,
                                            user.name
                                        ),
                                        React.createElement(
                                            "td",
                                            null,
                                            user.updated
                                        ),
                                        React.createElement(
                                            "td",
                                            null,
                                            user.created
                                        )
                                    );
                                })
                            )
                        )
                    )
                )
            );
        }
    }]);

    return ListAction;
}(React.Component);