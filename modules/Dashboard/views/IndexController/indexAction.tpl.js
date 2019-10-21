"use strict";

/**
 *
 */

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var IndexAction = function (_React$Component) {
    _inherits(IndexAction, _React$Component);

    function IndexAction(props) {
        _classCallCheck(this, IndexAction);

        var _this = _possibleConstructorReturn(this, (IndexAction.__proto__ || Object.getPrototypeOf(IndexAction)).call(this, props));

        alert(JSON.stringify(props));
        _this.state = { date: new Date() };
        return _this;
    }

    _createClass(IndexAction, [{
        key: "componentDidMount",
        value: function componentDidMount() {
            var _this2 = this;

            this.timerID = setInterval(function () {
                return _this2.tick();
            }, 1000);
        }
    }, {
        key: "componentWillUnmount",
        value: function componentWillUnmount() {
            clearInterval(this.timerID);
        }
    }, {
        key: "tick",
        value: function tick() {
            this.setState({
                date: new Date()
            });
        }
    }, {
        key: "render",
        value: function render() {
            return React.createElement(
                "div",
                { className: "widget box" },
                React.createElement(
                    "div",
                    { className: "widget-header" },
                    React.createElement(
                        "h4",
                        null,
                        React.createElement("i", { className: "icon-reorder" }),
                        "Hallo Welt"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "widget-content" },
                    React.createElement(
                        "p",
                        null,
                        "Es ist ",
                        this.state.date.toLocaleTimeString(),
                        "."
                    )
                )
            );
        }
    }]);

    return IndexAction;
}(React.Component);