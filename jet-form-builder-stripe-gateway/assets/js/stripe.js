/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./editor/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./editor/index.js":
/*!*************************!*\
  !*** ./editor/index.js ***!
  \*************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _main__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./main */ "./editor/main.js");
/* harmony import */ var _pay_now_scenario__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pay.now.scenario */ "./editor/pay.now.scenario.js");
/* harmony import */ var _subscription_scenario__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./subscription.scenario */ "./editor/subscription.scenario.js");



var _JetFBActions = JetFBActions,
  registerGateway = _JetFBActions.registerGateway;
var addFilter = wp.hooks.addFilter;
var __ = wp.i18n.__;
var gatewayID = 'stripe';
registerGateway(gatewayID, _main__WEBPACK_IMPORTED_MODULE_0__["default"]);
registerGateway(gatewayID, _pay_now_scenario__WEBPACK_IMPORTED_MODULE_1__["default"], 'PAY_NOW');
registerGateway(gatewayID, _subscription_scenario__WEBPACK_IMPORTED_MODULE_2__["default"], 'SUBSCRIPTION');
addFilter('jet.fb.gateways.getDisabledStateButton', 'jet-form-builder', function (isDisabled, props, issetActionType) {
  var _props$_jf_gateways;
  if (gatewayID === (props === null || props === void 0 || (_props$_jf_gateways = props._jf_gateways) === null || _props$_jf_gateways === void 0 ? void 0 : _props$_jf_gateways.gateway)) {
    return !issetActionType('save_record');
  }
  return isDisabled;
});
addFilter('jet.fb.gateways.getDisabledInfo', 'jet-form-builder', function (component, props) {
  var _props$_jf_gateways2;
  if (gatewayID !== (props === null || props === void 0 || (_props$_jf_gateways2 = props._jf_gateways) === null || _props$_jf_gateways2 === void 0 ? void 0 : _props$_jf_gateways2.gateway)) {
    return component;
  }
  return wp.element.createElement("p", null, __('Please add \`Save Form Record\` action', 'jet-form-builder'));
});

/***/ }),

/***/ "./editor/main.js":
/*!************************!*\
  !*** ./editor/main.js ***!
  \************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
var compose = wp.compose.compose;
var _wp$data = wp.data,
  withSelect = _wp$data.withSelect,
  withDispatch = _wp$data.withDispatch;
var _wp$components = wp.components,
  TextControl = _wp$components.TextControl,
  ToggleControl = _wp$components.ToggleControl,
  SelectControl = _wp$components.SelectControl,
  withNotices = _wp$components.withNotices;
var useEffect = wp.element.useEffect;
var _JetFBActions = JetFBActions,
  renderGateway = _JetFBActions.renderGateway;
var _JetFBHooks = JetFBHooks,
  withSelectGateways = _JetFBHooks.withSelectGateways,
  withDispatchGateways = _JetFBHooks.withDispatchGateways;
function StripeMain(_ref) {
  var setGatewayRequest = _ref.setGatewayRequest,
    gatewaySpecific = _ref.gatewaySpecific,
    setGatewaySpecific = _ref.setGatewaySpecific,
    gatewayScenario = _ref.gatewayScenario,
    setGatewayScenario = _ref.setGatewayScenario,
    getSpecificOrGlobal = _ref.getSpecificOrGlobal,
    additionalSourceGateway = _ref.additionalSourceGateway,
    specificGatewayLabel = _ref.specificGatewayLabel,
    noticeOperations = _ref.noticeOperations,
    noticeUI = _ref.noticeUI;
  var _gatewayScenario$id = gatewayScenario.id,
    scenario = _gatewayScenario$id === void 0 ? 'PAY_NOW' : _gatewayScenario$id;
  useEffect(function () {
    setGatewayRequest({
      id: scenario
    });
  }, [scenario]);
  useEffect(function () {
    setGatewayRequest({
      id: scenario
    });
  }, []);
  return wp.element.createElement(React.Fragment, null, noticeUI, wp.element.createElement(ToggleControl, {
    key: 'use_global',
    label: specificGatewayLabel('use_global'),
    checked: gatewaySpecific.use_global,
    onChange: function onChange(use_global) {
      return setGatewaySpecific({
        use_global: use_global
      });
    }
  }), wp.element.createElement(TextControl, {
    label: specificGatewayLabel('public'),
    key: "stripe_client_id_setting",
    value: getSpecificOrGlobal('public'),
    onChange: function onChange(value) {
      return setGatewaySpecific({
        public: value
      });
    },
    disabled: gatewaySpecific.use_global
  }), wp.element.createElement(TextControl, {
    label: specificGatewayLabel('secret'),
    key: "stripe_secret_setting",
    value: getSpecificOrGlobal('secret'),
    onChange: function onChange(secret) {
      return setGatewaySpecific({
        secret: secret
      });
    },
    disabled: gatewaySpecific.use_global
  }), wp.element.createElement(SelectControl, {
    labelPosition: "side",
    label: specificGatewayLabel('gateway_type'),
    value: scenario,
    onChange: function onChange(id) {
      setGatewayScenario({
        id: id
      });
    },
    options: additionalSourceGateway.scenarios
  }), renderGateway('stripe', {
    noticeOperations: noticeOperations
  }, scenario));
}
/* harmony default export */ __webpack_exports__["default"] = (compose(withSelect(withSelectGateways), withDispatch(withDispatchGateways), withNotices)(StripeMain));

/***/ }),

/***/ "./editor/pay.now.scenario.js":
/*!************************************!*\
  !*** ./editor/pay.now.scenario.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var compose = wp.compose.compose;
var _wp$data = wp.data,
  withSelect = _wp$data.withSelect,
  withDispatch = _wp$data.withDispatch;
var _wp$components = wp.components,
  TextControl = _wp$components.TextControl,
  SelectControl = _wp$components.SelectControl,
  BaseControl = _wp$components.BaseControl,
  RadioControl = _wp$components.RadioControl;
var _JetFBHooks = JetFBHooks,
  withSelectFormFields = _JetFBHooks.withSelectFormFields,
  withSelectGateways = _JetFBHooks.withSelectGateways,
  withDispatchGateways = _JetFBHooks.withDispatchGateways,
  withSelectActionsByType = _JetFBHooks.withSelectActionsByType;
var _JetFBComponents = JetFBComponents,
  GatewayFetchButton = _JetFBComponents.GatewayFetchButton;
function StripePayNowScenario(_ref) {
  var gatewayGeneral = _ref.gatewayGeneral,
    gatewaySpecific = _ref.gatewaySpecific,
    setGateway = _ref.setGateway,
    setGatewaySpecific = _ref.setGatewaySpecific,
    formFields = _ref.formFields,
    getSpecificOrGlobal = _ref.getSpecificOrGlobal,
    loadingGateway = _ref.loadingGateway,
    scenarioSource = _ref.scenarioSource,
    noticeOperations = _ref.noticeOperations,
    scenarioLabel = _ref.scenarioLabel,
    globalGatewayLabel = _ref.globalGatewayLabel;
  var displayNotice = function displayNotice(status) {
    return function (response) {
      noticeOperations.removeNotice(gatewayGeneral.gateway);
      noticeOperations.createNotice({
        status: status,
        content: response.message,
        id: gatewayGeneral.gateway
      });
    };
  };
  return wp.element.createElement(React.Fragment, null, wp.element.createElement(BaseControl, {
    label: scenarioLabel('fetch_button_label')
  }, wp.element.createElement("div", {
    className: "jet-user-fields-map__list"
  }, !loadingGateway.success && !loadingGateway.loading && wp.element.createElement("span", {
    className: 'description-controls'
  }, scenarioLabel('fetch_button_help')), wp.element.createElement(GatewayFetchButton, {
    initialLabel: scenarioLabel('fetch_button'),
    label: scenarioLabel('fetch_button_retry'),
    apiArgs: _objectSpread(_objectSpread({}, scenarioSource.fetch), {}, {
      data: {
        public: getSpecificOrGlobal('public'),
        secret: getSpecificOrGlobal('secret')
      }
    }),
    onFail: displayNotice('error')
  }))), loadingGateway.success && wp.element.createElement(React.Fragment, null, wp.element.createElement(TextControl, {
    label: scenarioLabel('currency'),
    key: "paypal_currency_code_setting",
    value: gatewaySpecific.currency,
    onChange: function onChange(currency) {
      return setGatewaySpecific({
        currency: currency
      });
    }
  }), wp.element.createElement(SelectControl, {
    label: globalGatewayLabel('price_field'),
    key: 'form_fields_price_field',
    value: gatewayGeneral.price_field,
    labelPosition: "side",
    onChange: function onChange(price_field) {
      setGateway({
        price_field: price_field
      });
    },
    options: formFields
  })));
}
/* harmony default export */ __webpack_exports__["default"] = (compose(withSelect(function () {
  return _objectSpread(_objectSpread({}, withSelectFormFields([], '--').apply(void 0, arguments)), withSelectGateways.apply(void 0, arguments));
}), withDispatch(function () {
  return _objectSpread({}, withDispatchGateways.apply(void 0, arguments));
}))(StripePayNowScenario));

/***/ }),

/***/ "./editor/subscription.scenario.js":
/*!*****************************************!*\
  !*** ./editor/subscription.scenario.js ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
var compose = wp.compose.compose;
var _wp$data = wp.data,
  withSelect = _wp$data.withSelect,
  withDispatch = _wp$data.withDispatch;
var _wp$components = wp.components,
  TextControl = _wp$components.TextControl,
  SelectControl = _wp$components.SelectControl,
  BaseControl = _wp$components.BaseControl,
  CustomSelectControl = _wp$components.CustomSelectControl,
  Button = _wp$components.Button;
var __ = wp.i18n.__;
var _JetFBHooks = JetFBHooks,
  withSelectFormFields = _JetFBHooks.withSelectFormFields,
  withSelectGateways = _JetFBHooks.withSelectGateways,
  withDispatchGateways = _JetFBHooks.withDispatchGateways;
var _JetFBActions = JetFBActions,
  sendGatewayRequest = _JetFBActions.sendGatewayRequest;
var _wp$element = wp.element,
  useState = _wp$element.useState,
  useEffect = _wp$element.useEffect,
  useMemo = _wp$element.useMemo;
function StripeSubscriptionScenario(_ref) {
  var _currentScenario$plan;
  var gatewayGeneral = _ref.gatewayGeneral,
    gatewaySpecific = _ref.gatewaySpecific,
    setGateway = _ref.setGateway,
    setGatewaySpecific = _ref.setGatewaySpecific,
    formFields = _ref.formFields,
    getSpecificOrGlobal = _ref.getSpecificOrGlobal,
    scenarioSource = _ref.scenarioSource,
    noticeOperations = _ref.noticeOperations,
    scenarioLabel = _ref.scenarioLabel,
    globalGatewayLabel = _ref.globalGatewayLabel,
    currentScenario = _ref.currentScenario,
    setScenario = _ref.setScenario;
  var _useState = useState(false),
    _useState2 = _slicedToArray(_useState, 2),
    isRefreshing = _useState2[0],
    setIsRefreshing = _useState2[1];
  var _useState3 = useState(''),
    _useState4 = _slicedToArray(_useState3, 2),
    noticeText = _useState4[0],
    setNoticeText = _useState4[1];
  var _useState5 = useState(''),
    _useState6 = _slicedToArray(_useState5, 2),
    noticeStatus = _useState6[0],
    setNoticeStatus = _useState6[1];
  var _useState7 = useState([]),
    _useState8 = _slicedToArray(_useState7, 2),
    plans = _useState8[0],
    setPlans = _useState8[1];
  var fetchPlans = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
      var _ref3,
        _ref3$forceRefresh,
        forceRefresh,
        response,
        _args = arguments;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            _ref3 = _args.length > 0 && _args[0] !== undefined ? _args[0] : {}, _ref3$forceRefresh = _ref3.forceRefresh, forceRefresh = _ref3$forceRefresh === void 0 ? false : _ref3$forceRefresh;
            _context.n = 1;
            return wp.apiRequest({
              path: '/jet-form-builder/v1/fetch-stripe-plans',
              method: 'POST',
              data: {
                public: getSpecificOrGlobal('public'),
                secret: getSpecificOrGlobal('secret'),
                force_refresh: forceRefresh
              }
            });
          case 1:
            response = _context.v;
            setPlans(response.data || []);
            return _context.a(2, response);
        }
      }, _callee);
    }));
    return function fetchPlans() {
      return _ref2.apply(this, arguments);
    };
  }();
  useEffect(function () {
    setIsRefreshing(true);
    fetchPlans().finally(function () {
      return setIsRefreshing(false);
    });
  }, []);
  useEffect(function () {
    if (currentScenario.plan_field === undefined || currentScenario.plan_field === null) {
      setScenario({
        plan_field: ''
      });
    }
  }, []);
  var selectOptions = useMemo(function () {
    return (plans || []).map(function (plan) {
      return {
        name: plan.label,
        label: plan.label,
        key: plan.key || plan.id,
        disabled: !!plan.disabled
      };
    });
  }, [plans]);
  var getPlan = function getPlan(planID) {
    return selectOptions.find(function (opt) {
      return opt.key === planID;
    });
  };
  var currentPlan = getPlan(currentScenario.plan_manual);
  useEffect(function () {
    if ((currentScenario.plan_field === '' || !currentScenario.plan_field) && !currentScenario.plan_manual && selectOptions.length) {
      var firstEnabled = selectOptions.find(function (opt) {
        return !opt.disabled;
      });
      if (firstEnabled) {
        setScenario({
          plan_manual: firstEnabled.key
        });
      }
    }
  }, [selectOptions, currentScenario.plan_field, currentScenario.plan_manual, setScenario]);
  var handleRefreshPlans = function handleRefreshPlans() {
    setIsRefreshing(true);
    setNoticeText('');
    setNoticeStatus('');
    fetchPlans({
      forceRefresh: true
    }).then(function () {
      setNoticeText(scenarioLabel('plans_fetched_successfully'));
      setNoticeStatus('success');
    }).catch(function (error) {
      var msg = (error === null || error === void 0 ? void 0 : error.message) || 'Request failed';
      setNoticeText(msg);
      setNoticeStatus('error');
    }).finally(function () {
      setIsRefreshing(false);
    });
  };
  return wp.element.createElement(React.Fragment, null, wp.element.createElement(SelectControl, {
    label: scenarioLabel('subscribe_plan_field'),
    key: 'form_fields_subscribe_plan_field',
    value: (_currentScenario$plan = currentScenario.plan_field) !== null && _currentScenario$plan !== void 0 ? _currentScenario$plan : '',
    labelPosition: "side",
    onChange: function onChange(plan_field) {
      return setScenario({
        plan_field: plan_field
      });
    },
    options: formFields
  }), !currentScenario.plan_field && wp.element.createElement(React.Fragment, null, wp.element.createElement(BaseControl, {
    label: scenarioLabel('subscribe_plan')
  }, wp.element.createElement(CustomSelectControl, {
    hideLabelFromVision: true,
    options: selectOptions,
    value: currentPlan,
    onChange: function onChange(_ref4) {
      var selectedItem = _ref4.selectedItem;
      if (selectedItem !== null && selectedItem !== void 0 && selectedItem.disabled) {
        return;
      }
      setScenario({
        plan_manual: selectedItem.key
      });
    }
  })), wp.element.createElement(Button, {
    isSecondary: true,
    isBusy: isRefreshing,
    disabled: isRefreshing,
    onClick: handleRefreshPlans
  }, scenarioLabel('refresh_plans_button')), noticeText && wp.element.createElement("div", {
    style: {
      color: noticeStatus === 'success' ? 'green' : 'red',
      marginTop: '0.5em',
      fontSize: '13px'
    }
  }, noticeText)));
}
/* harmony default export */ __webpack_exports__["default"] = (compose(withSelect(function () {
  return _objectSpread(_objectSpread({}, withSelectFormFields([], __('Manual Input', 'jet-form-builder')).apply(void 0, arguments)), withSelectGateways.apply(void 0, arguments));
}), withDispatch(function () {
  return _objectSpread({}, withDispatchGateways.apply(void 0, arguments));
}))(StripeSubscriptionScenario));

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3RyaXBlLmpzIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vL3dlYnBhY2svYm9vdHN0cmFwIiwid2VicGFjazovLy8uL2VkaXRvci9pbmRleC5qcyIsIndlYnBhY2s6Ly8vLi9lZGl0b3IvbWFpbi5qcyIsIndlYnBhY2s6Ly8vLi9lZGl0b3IvcGF5Lm5vdy5zY2VuYXJpby5qcyIsIndlYnBhY2s6Ly8vLi9lZGl0b3Ivc3Vic2NyaXB0aW9uLnNjZW5hcmlvLmpzIl0sInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGdldHRlciB9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yID0gZnVuY3Rpb24oZXhwb3J0cykge1xuIFx0XHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcbiBcdFx0fVxuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xuIFx0fTtcblxuIFx0Ly8gY3JlYXRlIGEgZmFrZSBuYW1lc3BhY2Ugb2JqZWN0XG4gXHQvLyBtb2RlICYgMTogdmFsdWUgaXMgYSBtb2R1bGUgaWQsIHJlcXVpcmUgaXRcbiBcdC8vIG1vZGUgJiAyOiBtZXJnZSBhbGwgcHJvcGVydGllcyBvZiB2YWx1ZSBpbnRvIHRoZSBuc1xuIFx0Ly8gbW9kZSAmIDQ6IHJldHVybiB2YWx1ZSB3aGVuIGFscmVhZHkgbnMgb2JqZWN0XG4gXHQvLyBtb2RlICYgOHwxOiBiZWhhdmUgbGlrZSByZXF1aXJlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnQgPSBmdW5jdGlvbih2YWx1ZSwgbW9kZSkge1xuIFx0XHRpZihtb2RlICYgMSkgdmFsdWUgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKHZhbHVlKTtcbiBcdFx0aWYobW9kZSAmIDgpIHJldHVybiB2YWx1ZTtcbiBcdFx0aWYoKG1vZGUgJiA0KSAmJiB0eXBlb2YgdmFsdWUgPT09ICdvYmplY3QnICYmIHZhbHVlICYmIHZhbHVlLl9fZXNNb2R1bGUpIHJldHVybiB2YWx1ZTtcbiBcdFx0dmFyIG5zID0gT2JqZWN0LmNyZWF0ZShudWxsKTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yKG5zKTtcbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG5zLCAnZGVmYXVsdCcsIHsgZW51bWVyYWJsZTogdHJ1ZSwgdmFsdWU6IHZhbHVlIH0pO1xuIFx0XHRpZihtb2RlICYgMiAmJiB0eXBlb2YgdmFsdWUgIT0gJ3N0cmluZycpIGZvcih2YXIga2V5IGluIHZhbHVlKSBfX3dlYnBhY2tfcmVxdWlyZV9fLmQobnMsIGtleSwgZnVuY3Rpb24oa2V5KSB7IHJldHVybiB2YWx1ZVtrZXldOyB9LmJpbmQobnVsbCwga2V5KSk7XG4gXHRcdHJldHVybiBucztcbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiXCI7XG5cblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSBcIi4vZWRpdG9yL2luZGV4LmpzXCIpO1xuIiwiaW1wb3J0IFN0cmlwZU1haW4gZnJvbSAnLi9tYWluJztcclxuaW1wb3J0IFN0cmlwZVBheU5vd1NjZW5hcmlvIGZyb20gJy4vcGF5Lm5vdy5zY2VuYXJpbyc7XHJcbmltcG9ydCBTdHJpcGVTdWJzY3JpcHRpb25TY2VuYXJpbyBmcm9tICcuL3N1YnNjcmlwdGlvbi5zY2VuYXJpbyc7XHJcblxyXG5jb25zdCB7XHJcblx0cmVnaXN0ZXJHYXRld2F5LFxyXG59ID0gSmV0RkJBY3Rpb25zO1xyXG5cclxuY29uc3Qge1xyXG5cdGFkZEZpbHRlcixcclxufSA9IHdwLmhvb2tzO1xyXG5cclxuY29uc3QgeyBfXyB9ID0gd3AuaTE4bjtcclxuXHJcbmNvbnN0IGdhdGV3YXlJRCA9ICdzdHJpcGUnO1xyXG5cclxucmVnaXN0ZXJHYXRld2F5KFxyXG5cdGdhdGV3YXlJRCxcclxuXHRTdHJpcGVNYWluLFxyXG4pO1xyXG5cclxucmVnaXN0ZXJHYXRld2F5KFxyXG5cdGdhdGV3YXlJRCxcclxuXHRTdHJpcGVQYXlOb3dTY2VuYXJpbyxcclxuXHQnUEFZX05PVycsXHJcbik7XHJcblxyXG5yZWdpc3RlckdhdGV3YXkoXHJcblx0Z2F0ZXdheUlELFxyXG5cdFN0cmlwZVN1YnNjcmlwdGlvblNjZW5hcmlvLFxyXG5cdCdTVUJTQ1JJUFRJT04nLFxyXG4pO1xyXG5cclxuYWRkRmlsdGVyKCAnamV0LmZiLmdhdGV3YXlzLmdldERpc2FibGVkU3RhdGVCdXR0b24nLCAnamV0LWZvcm0tYnVpbGRlcicsICggaXNEaXNhYmxlZCwgcHJvcHMsIGlzc2V0QWN0aW9uVHlwZSApID0+IHtcclxuXHRpZiAoIGdhdGV3YXlJRCA9PT0gcHJvcHM/Ll9qZl9nYXRld2F5cz8uZ2F0ZXdheSApIHtcclxuXHRcdHJldHVybiAhIGlzc2V0QWN0aW9uVHlwZSggJ3NhdmVfcmVjb3JkJyApO1xyXG5cdH1cclxuXHJcblx0cmV0dXJuIGlzRGlzYWJsZWQ7XHJcbn0gKTtcclxuXHJcbmFkZEZpbHRlciggJ2pldC5mYi5nYXRld2F5cy5nZXREaXNhYmxlZEluZm8nLCAnamV0LWZvcm0tYnVpbGRlcicsICggY29tcG9uZW50LCBwcm9wcyApID0+IHtcclxuXHRpZiAoIGdhdGV3YXlJRCAhPT0gcHJvcHM/Ll9qZl9nYXRld2F5cz8uZ2F0ZXdheSApIHtcclxuXHRcdHJldHVybiBjb21wb25lbnQ7XHJcblx0fVxyXG5cclxuXHRyZXR1cm4gPHA+eyBfXyggJ1BsZWFzZSBhZGQgXFxgU2F2ZSBGb3JtIFJlY29yZFxcYCBhY3Rpb24nLCAnamV0LWZvcm0tYnVpbGRlcicgKSB9PC9wPlxyXG59ICk7IiwiY29uc3QgeyBjb21wb3NlIH0gPSB3cC5jb21wb3NlO1xyXG5cclxuY29uc3Qge1xyXG5cdHdpdGhTZWxlY3QsXHJcblx0d2l0aERpc3BhdGNoLFxyXG59ID0gd3AuZGF0YTtcclxuXHJcbmNvbnN0IHtcclxuXHRUZXh0Q29udHJvbCxcclxuXHRUb2dnbGVDb250cm9sLFxyXG5cdFNlbGVjdENvbnRyb2wsXHJcblx0d2l0aE5vdGljZXMsXHJcbn0gPSB3cC5jb21wb25lbnRzO1xyXG5cclxuY29uc3Qge1xyXG5cdHVzZUVmZmVjdCxcclxufSA9IHdwLmVsZW1lbnQ7XHJcblxyXG5jb25zdCB7XHJcblx0cmVuZGVyR2F0ZXdheSxcclxufSA9IEpldEZCQWN0aW9ucztcclxuXHJcbmNvbnN0IHtcclxuXHR3aXRoU2VsZWN0R2F0ZXdheXMsXHJcblx0d2l0aERpc3BhdGNoR2F0ZXdheXMsXHJcbn0gPSBKZXRGQkhvb2tzO1xyXG5cclxuZnVuY3Rpb24gU3RyaXBlTWFpbigge1xyXG5cdHNldEdhdGV3YXlSZXF1ZXN0LFxyXG5cdGdhdGV3YXlTcGVjaWZpYyxcclxuXHRzZXRHYXRld2F5U3BlY2lmaWMsXHJcblx0Z2F0ZXdheVNjZW5hcmlvLFxyXG5cdHNldEdhdGV3YXlTY2VuYXJpbyxcclxuXHRnZXRTcGVjaWZpY09yR2xvYmFsLFxyXG5cdGFkZGl0aW9uYWxTb3VyY2VHYXRld2F5LFxyXG5cdHNwZWNpZmljR2F0ZXdheUxhYmVsLFxyXG5cdG5vdGljZU9wZXJhdGlvbnMsXHJcblx0bm90aWNlVUksXHJcbn0gKSB7XHJcblxyXG5cdGNvbnN0IHtcclxuXHRcdGlkOiBzY2VuYXJpbyA9ICdQQVlfTk9XJyxcclxuXHR9ID0gZ2F0ZXdheVNjZW5hcmlvO1xyXG5cclxuXHR1c2VFZmZlY3QoICgpID0+IHtcclxuXHRcdHNldEdhdGV3YXlSZXF1ZXN0KCB7IGlkOiBzY2VuYXJpbyB9ICk7XHJcblx0fSwgWyBzY2VuYXJpbyBdICk7XHJcblxyXG5cdHVzZUVmZmVjdCggKCkgPT4ge1xyXG5cdFx0c2V0R2F0ZXdheVJlcXVlc3QoIHsgaWQ6IHNjZW5hcmlvIH0gKTtcclxuXHR9LCBbXSApO1xyXG5cclxuXHRyZXR1cm4gPD5cclxuXHRcdHsgbm90aWNlVUkgfVxyXG5cdFx0PFRvZ2dsZUNvbnRyb2xcclxuXHRcdFx0a2V5PXsgJ3VzZV9nbG9iYWwnIH1cclxuXHRcdFx0bGFiZWw9eyBzcGVjaWZpY0dhdGV3YXlMYWJlbCggJ3VzZV9nbG9iYWwnICkgfVxyXG5cdFx0XHRjaGVja2VkPXsgZ2F0ZXdheVNwZWNpZmljLnVzZV9nbG9iYWwgfVxyXG5cdFx0XHRvbkNoYW5nZT17IHVzZV9nbG9iYWwgPT4gc2V0R2F0ZXdheVNwZWNpZmljKCB7IHVzZV9nbG9iYWwgfSApIH1cclxuXHRcdC8+XHJcblx0XHQ8VGV4dENvbnRyb2xcclxuXHRcdFx0bGFiZWw9eyBzcGVjaWZpY0dhdGV3YXlMYWJlbCggJ3B1YmxpYycgKSB9XHJcblx0XHRcdGtleT0nc3RyaXBlX2NsaWVudF9pZF9zZXR0aW5nJ1xyXG5cdFx0XHR2YWx1ZT17IGdldFNwZWNpZmljT3JHbG9iYWwoICdwdWJsaWMnICkgfVxyXG5cdFx0XHRvbkNoYW5nZT17IHZhbHVlID0+IHNldEdhdGV3YXlTcGVjaWZpYyggeyBwdWJsaWM6IHZhbHVlIH0gKSB9XHJcblx0XHRcdGRpc2FibGVkPXsgZ2F0ZXdheVNwZWNpZmljLnVzZV9nbG9iYWwgfVxyXG5cdFx0Lz5cclxuXHRcdDxUZXh0Q29udHJvbFxyXG5cdFx0XHRsYWJlbD17IHNwZWNpZmljR2F0ZXdheUxhYmVsKCAnc2VjcmV0JyApIH1cclxuXHRcdFx0a2V5PSdzdHJpcGVfc2VjcmV0X3NldHRpbmcnXHJcblx0XHRcdHZhbHVlPXsgZ2V0U3BlY2lmaWNPckdsb2JhbCggJ3NlY3JldCcgKSB9XHJcblx0XHRcdG9uQ2hhbmdlPXsgc2VjcmV0ID0+IHNldEdhdGV3YXlTcGVjaWZpYyggeyBzZWNyZXQgfSApIH1cclxuXHRcdFx0ZGlzYWJsZWQ9eyBnYXRld2F5U3BlY2lmaWMudXNlX2dsb2JhbCB9XHJcblx0XHQvPlxyXG5cdFx0PFNlbGVjdENvbnRyb2xcclxuXHRcdFx0bGFiZWxQb3NpdGlvbj0nc2lkZSdcclxuXHRcdFx0bGFiZWw9eyBzcGVjaWZpY0dhdGV3YXlMYWJlbCggJ2dhdGV3YXlfdHlwZScgKSB9XHJcblx0XHRcdHZhbHVlPXsgc2NlbmFyaW8gfVxyXG5cdFx0XHRvbkNoYW5nZT17IGlkID0+IHtcclxuXHRcdFx0XHRzZXRHYXRld2F5U2NlbmFyaW8oIHsgaWQgfSApO1xyXG5cdFx0XHR9IH1cclxuXHRcdFx0b3B0aW9ucz17IGFkZGl0aW9uYWxTb3VyY2VHYXRld2F5LnNjZW5hcmlvcyB9XHJcblx0XHQvPlxyXG5cdFx0eyByZW5kZXJHYXRld2F5KCAnc3RyaXBlJywgeyBub3RpY2VPcGVyYXRpb25zIH0sIHNjZW5hcmlvICkgfVxyXG5cdDwvPjtcclxufVxyXG5cclxuZXhwb3J0IGRlZmF1bHQgY29tcG9zZShcclxuXHR3aXRoU2VsZWN0KCB3aXRoU2VsZWN0R2F0ZXdheXMgKSxcclxuXHR3aXRoRGlzcGF0Y2goIHdpdGhEaXNwYXRjaEdhdGV3YXlzICksXHJcblx0d2l0aE5vdGljZXMsXHJcbikoIFN0cmlwZU1haW4gKTsiLCJjb25zdCB7IGNvbXBvc2UgfSA9IHdwLmNvbXBvc2U7XHJcblxyXG5jb25zdCB7XHJcblx0d2l0aFNlbGVjdCxcclxuXHR3aXRoRGlzcGF0Y2gsXHJcbn0gPSB3cC5kYXRhO1xyXG5cclxuY29uc3Qge1xyXG5cdFRleHRDb250cm9sLFxyXG5cdFNlbGVjdENvbnRyb2wsXHJcblx0QmFzZUNvbnRyb2wsXHJcblx0UmFkaW9Db250cm9sLFxyXG59ID0gd3AuY29tcG9uZW50cztcclxuXHJcbmNvbnN0IHtcclxuXHR3aXRoU2VsZWN0Rm9ybUZpZWxkcyxcclxuXHR3aXRoU2VsZWN0R2F0ZXdheXMsXHJcblx0d2l0aERpc3BhdGNoR2F0ZXdheXMsXHJcblx0d2l0aFNlbGVjdEFjdGlvbnNCeVR5cGUsXHJcbn0gPSBKZXRGQkhvb2tzO1xyXG5cclxuY29uc3QgeyBHYXRld2F5RmV0Y2hCdXR0b24gfSA9IEpldEZCQ29tcG9uZW50cztcclxuXHJcbmZ1bmN0aW9uIFN0cmlwZVBheU5vd1NjZW5hcmlvKCB7XHJcblx0Z2F0ZXdheUdlbmVyYWwsXHJcblx0Z2F0ZXdheVNwZWNpZmljLFxyXG5cdHNldEdhdGV3YXksXHJcblx0c2V0R2F0ZXdheVNwZWNpZmljLFxyXG5cdGZvcm1GaWVsZHMsXHJcblx0Z2V0U3BlY2lmaWNPckdsb2JhbCxcclxuXHRsb2FkaW5nR2F0ZXdheSxcclxuXHRzY2VuYXJpb1NvdXJjZSxcclxuXHRub3RpY2VPcGVyYXRpb25zLFxyXG5cdHNjZW5hcmlvTGFiZWwsXHJcblx0Z2xvYmFsR2F0ZXdheUxhYmVsLFxyXG59ICkge1xyXG5cclxuXHRjb25zdCBkaXNwbGF5Tm90aWNlID0gc3RhdHVzID0+IHJlc3BvbnNlID0+IHtcclxuXHRcdG5vdGljZU9wZXJhdGlvbnMucmVtb3ZlTm90aWNlKCBnYXRld2F5R2VuZXJhbC5nYXRld2F5ICk7XHJcblx0XHRub3RpY2VPcGVyYXRpb25zLmNyZWF0ZU5vdGljZSgge1xyXG5cdFx0XHRzdGF0dXMsXHJcblx0XHRcdGNvbnRlbnQ6IHJlc3BvbnNlLm1lc3NhZ2UsXHJcblx0XHRcdGlkOiBnYXRld2F5R2VuZXJhbC5nYXRld2F5LFxyXG5cdFx0fSApO1xyXG5cdH07XHJcblxyXG5cdHJldHVybiA8PlxyXG5cdFx0PEJhc2VDb250cm9sXHJcblx0XHRcdGxhYmVsPXsgc2NlbmFyaW9MYWJlbCggJ2ZldGNoX2J1dHRvbl9sYWJlbCcgKSB9XHJcblx0XHQ+XHJcblx0XHRcdDxkaXYgY2xhc3NOYW1lPVwiamV0LXVzZXItZmllbGRzLW1hcF9fbGlzdFwiPlxyXG5cdFx0XHRcdHsgKCAhIGxvYWRpbmdHYXRld2F5LnN1Y2Nlc3MgJiYgISBsb2FkaW5nR2F0ZXdheS5sb2FkaW5nICkgJiYgPHNwYW5cclxuXHRcdFx0XHRcdGNsYXNzTmFtZT17ICdkZXNjcmlwdGlvbi1jb250cm9scycgfVxyXG5cdFx0XHRcdD5cclxuXHRcdFx0XHRcdHsgc2NlbmFyaW9MYWJlbCggJ2ZldGNoX2J1dHRvbl9oZWxwJyApIH1cclxuXHRcdFx0XHQ8L3NwYW4+IH1cclxuXHRcdFx0XHQ8R2F0ZXdheUZldGNoQnV0dG9uXHJcblx0XHRcdFx0XHRpbml0aWFsTGFiZWw9eyBzY2VuYXJpb0xhYmVsKCAnZmV0Y2hfYnV0dG9uJyApIH1cclxuXHRcdFx0XHRcdGxhYmVsPXsgc2NlbmFyaW9MYWJlbCggJ2ZldGNoX2J1dHRvbl9yZXRyeScgKSB9XHJcblx0XHRcdFx0XHRhcGlBcmdzPXsge1xyXG5cdFx0XHRcdFx0XHQuLi5zY2VuYXJpb1NvdXJjZS5mZXRjaCxcclxuXHRcdFx0XHRcdFx0ZGF0YToge1xyXG5cdFx0XHRcdFx0XHRcdHB1YmxpYzogZ2V0U3BlY2lmaWNPckdsb2JhbCggJ3B1YmxpYycgKSxcclxuXHRcdFx0XHRcdFx0XHRzZWNyZXQ6IGdldFNwZWNpZmljT3JHbG9iYWwoICdzZWNyZXQnICksXHJcblx0XHRcdFx0XHRcdH0sXHJcblx0XHRcdFx0XHR9IH1cclxuXHRcdFx0XHRcdG9uRmFpbD17IGRpc3BsYXlOb3RpY2UoICdlcnJvcicgKSB9XHJcblx0XHRcdFx0Lz5cclxuXHRcdFx0PC9kaXY+XHJcblx0XHQ8L0Jhc2VDb250cm9sPlxyXG5cdFx0eyBsb2FkaW5nR2F0ZXdheS5zdWNjZXNzICYmIDw+XHJcblx0XHRcdDxUZXh0Q29udHJvbFxyXG5cdFx0XHRcdGxhYmVsPXsgc2NlbmFyaW9MYWJlbCggJ2N1cnJlbmN5JyApIH1cclxuXHRcdFx0XHRrZXk9J3BheXBhbF9jdXJyZW5jeV9jb2RlX3NldHRpbmcnXHJcblx0XHRcdFx0dmFsdWU9eyBnYXRld2F5U3BlY2lmaWMuY3VycmVuY3kgfVxyXG5cdFx0XHRcdG9uQ2hhbmdlPXsgY3VycmVuY3kgPT4gc2V0R2F0ZXdheVNwZWNpZmljKCB7IGN1cnJlbmN5IH0gKSB9XHJcblx0XHRcdC8+XHJcblx0XHRcdDxTZWxlY3RDb250cm9sXHJcblx0XHRcdFx0bGFiZWw9eyBnbG9iYWxHYXRld2F5TGFiZWwoICdwcmljZV9maWVsZCcgKSB9XHJcblx0XHRcdFx0a2V5PXsgJ2Zvcm1fZmllbGRzX3ByaWNlX2ZpZWxkJyB9XHJcblx0XHRcdFx0dmFsdWU9eyBnYXRld2F5R2VuZXJhbC5wcmljZV9maWVsZCB9XHJcblx0XHRcdFx0bGFiZWxQb3NpdGlvbj0nc2lkZSdcclxuXHRcdFx0XHRvbkNoYW5nZT17IHByaWNlX2ZpZWxkID0+IHtcclxuXHRcdFx0XHRcdHNldEdhdGV3YXkoIHsgcHJpY2VfZmllbGQgfSApO1xyXG5cdFx0XHRcdH0gfVxyXG5cdFx0XHRcdG9wdGlvbnM9eyBmb3JtRmllbGRzIH1cclxuXHRcdFx0Lz5cclxuXHRcdDwvPiB9XHJcblx0PC8+O1xyXG59XHJcblxyXG5leHBvcnQgZGVmYXVsdCBjb21wb3NlKFxyXG5cdHdpdGhTZWxlY3QoICggLi4ucHJvcHMgKSA9PiAoXHJcblx0XHR7XHJcblx0XHRcdC4uLndpdGhTZWxlY3RGb3JtRmllbGRzKCBbXSwgJy0tJyApKCAuLi5wcm9wcyApLFxyXG5cdFx0XHQuLi53aXRoU2VsZWN0R2F0ZXdheXMoIC4uLnByb3BzICksXHJcblx0XHR9XHJcblx0KSApLFxyXG5cdHdpdGhEaXNwYXRjaCggKCAuLi5wcm9wcyApID0+IChcclxuXHRcdHtcclxuXHRcdFx0Li4ud2l0aERpc3BhdGNoR2F0ZXdheXMoIC4uLnByb3BzICksXHJcblx0XHR9XHJcblx0KSApLFxyXG4pKCBTdHJpcGVQYXlOb3dTY2VuYXJpbyApOyIsImNvbnN0IHsgY29tcG9zZSB9ID0gd3AuY29tcG9zZTtcclxuY29uc3QgeyB3aXRoU2VsZWN0LCB3aXRoRGlzcGF0Y2ggfSA9IHdwLmRhdGE7XHJcbmNvbnN0IHtcclxuXHRUZXh0Q29udHJvbCxcclxuXHRTZWxlY3RDb250cm9sLFxyXG5cdEJhc2VDb250cm9sLFxyXG5cdEN1c3RvbVNlbGVjdENvbnRyb2wsXHJcblx0QnV0dG9uLFxyXG59ID0gd3AuY29tcG9uZW50cztcclxuY29uc3QgeyBfXyB9ID0gd3AuaTE4bjtcclxuY29uc3Qge1xyXG5cdHdpdGhTZWxlY3RGb3JtRmllbGRzLFxyXG5cdHdpdGhTZWxlY3RHYXRld2F5cyxcclxuXHR3aXRoRGlzcGF0Y2hHYXRld2F5cyxcclxufSA9IEpldEZCSG9va3M7XHJcbmNvbnN0IHsgc2VuZEdhdGV3YXlSZXF1ZXN0IH0gPSBKZXRGQkFjdGlvbnM7XHJcbmNvbnN0IHsgdXNlU3RhdGUsIHVzZUVmZmVjdCwgdXNlTWVtbyB9ID0gd3AuZWxlbWVudDtcclxuXHJcbmZ1bmN0aW9uIFN0cmlwZVN1YnNjcmlwdGlvblNjZW5hcmlvKCB7XHJcblx0XHRcdFx0XHRcdFx0XHRcdFx0IGdhdGV3YXlHZW5lcmFsLFxyXG5cdFx0XHRcdFx0XHRcdFx0XHRcdCBnYXRld2F5U3BlY2lmaWMsXHJcblx0XHRcdFx0XHRcdFx0XHRcdFx0IHNldEdhdGV3YXksXHJcblx0XHRcdFx0XHRcdFx0XHRcdFx0IHNldEdhdGV3YXlTcGVjaWZpYyxcclxuXHRcdFx0XHRcdFx0XHRcdFx0XHQgZm9ybUZpZWxkcyxcclxuXHRcdFx0XHRcdFx0XHRcdFx0XHQgZ2V0U3BlY2lmaWNPckdsb2JhbCxcclxuXHRcdFx0XHRcdFx0XHRcdFx0XHQgc2NlbmFyaW9Tb3VyY2UsXHJcblx0XHRcdFx0XHRcdFx0XHRcdFx0IG5vdGljZU9wZXJhdGlvbnMsXHJcblx0XHRcdFx0XHRcdFx0XHRcdFx0IHNjZW5hcmlvTGFiZWwsXHJcblx0XHRcdFx0XHRcdFx0XHRcdFx0IGdsb2JhbEdhdGV3YXlMYWJlbCxcclxuXHRcdFx0XHRcdFx0XHRcdFx0XHQgY3VycmVudFNjZW5hcmlvLFxyXG5cdFx0XHRcdFx0XHRcdFx0XHRcdCBzZXRTY2VuYXJpbyxcclxuXHRcdFx0XHRcdFx0XHRcdFx0IH0gKSB7XHJcblx0Y29uc3QgWyBpc1JlZnJlc2hpbmcsIHNldElzUmVmcmVzaGluZyBdID0gdXNlU3RhdGUoIGZhbHNlICk7XHJcblx0Y29uc3QgWyBub3RpY2VUZXh0LCBzZXROb3RpY2VUZXh0IF0gPSB1c2VTdGF0ZSggJycgKTtcclxuXHRjb25zdCBbIG5vdGljZVN0YXR1cywgc2V0Tm90aWNlU3RhdHVzIF0gPSB1c2VTdGF0ZSggJycgKTtcclxuXHRjb25zdCBbIHBsYW5zLCBzZXRQbGFucyBdID0gdXNlU3RhdGUoIFtdICk7XHJcblxyXG5cdGNvbnN0IGZldGNoUGxhbnMgPSBhc3luYyAoIHsgZm9yY2VSZWZyZXNoID0gZmFsc2UgfSA9IHt9ICkgPT4ge1xyXG5cdFx0Y29uc3QgcmVzcG9uc2UgPSBhd2FpdCB3cC5hcGlSZXF1ZXN0KCB7XHJcblx0XHRcdHBhdGg6ICcvamV0LWZvcm0tYnVpbGRlci92MS9mZXRjaC1zdHJpcGUtcGxhbnMnLFxyXG5cdFx0XHRtZXRob2Q6ICdQT1NUJyxcclxuXHRcdFx0ZGF0YToge1xyXG5cdFx0XHRcdHB1YmxpYzogZ2V0U3BlY2lmaWNPckdsb2JhbCggJ3B1YmxpYycgKSxcclxuXHRcdFx0XHRzZWNyZXQ6IGdldFNwZWNpZmljT3JHbG9iYWwoICdzZWNyZXQnICksXHJcblx0XHRcdFx0Zm9yY2VfcmVmcmVzaDogZm9yY2VSZWZyZXNoLFxyXG5cdFx0XHR9LFxyXG5cdFx0fSApO1xyXG5cclxuXHRcdHNldFBsYW5zKCByZXNwb25zZS5kYXRhIHx8IFtdICk7XHJcblx0XHRyZXR1cm4gcmVzcG9uc2U7XHJcblx0fTtcclxuXHJcblx0dXNlRWZmZWN0KCAoKSA9PiB7XHJcblx0XHRzZXRJc1JlZnJlc2hpbmcoIHRydWUgKTtcclxuXHRcdGZldGNoUGxhbnMoKS5maW5hbGx5KCAoKSA9PiBzZXRJc1JlZnJlc2hpbmcoIGZhbHNlICkgKTtcclxuXHR9LCBbXSApO1xyXG5cclxuXHR1c2VFZmZlY3QoICgpID0+IHtcclxuXHRcdGlmICggY3VycmVudFNjZW5hcmlvLnBsYW5fZmllbGQgPT09IHVuZGVmaW5lZCB8fCBjdXJyZW50U2NlbmFyaW8ucGxhbl9maWVsZCA9PT0gbnVsbCApIHtcclxuXHRcdFx0c2V0U2NlbmFyaW8oIHsgcGxhbl9maWVsZDogJycgfSApO1xyXG5cdFx0fVxyXG5cdH0sIFtdICk7XHJcblxyXG5cdGNvbnN0IHNlbGVjdE9wdGlvbnMgPSB1c2VNZW1vKCAoKSA9PiB7XHJcblx0XHRyZXR1cm4gKCBwbGFucyB8fCBbXSApLm1hcCggKCBwbGFuICkgPT4gKCB7XHJcblx0XHRcdG5hbWU6IHBsYW4ubGFiZWwsXHJcblx0XHRcdGxhYmVsOiBwbGFuLmxhYmVsLFxyXG5cdFx0XHRrZXk6ICBwbGFuLmtleSB8fCBwbGFuLmlkLFxyXG5cdFx0XHRkaXNhYmxlZDogISFwbGFuLmRpc2FibGVkLFxyXG5cdFx0fSApICk7XHJcblx0fSwgWyBwbGFucyBdICk7XHJcblxyXG5cdGNvbnN0IGdldFBsYW4gPSAoIHBsYW5JRCApID0+IHNlbGVjdE9wdGlvbnMuZmluZCggKCBvcHQgKSA9PiBvcHQua2V5ID09PSBwbGFuSUQgKTtcclxuXHRjb25zdCBjdXJyZW50UGxhbiA9IGdldFBsYW4oIGN1cnJlbnRTY2VuYXJpby5wbGFuX21hbnVhbCApO1xyXG5cclxuXHR1c2VFZmZlY3QoICgpID0+IHtcclxuXHRcdGlmIChcclxuXHRcdFx0KCBjdXJyZW50U2NlbmFyaW8ucGxhbl9maWVsZCA9PT0gJycgfHwgISBjdXJyZW50U2NlbmFyaW8ucGxhbl9maWVsZCApICYmXHJcblx0XHRcdCEgY3VycmVudFNjZW5hcmlvLnBsYW5fbWFudWFsICYmXHJcblx0XHRcdHNlbGVjdE9wdGlvbnMubGVuZ3RoXHJcblx0XHQpIHtcclxuXHRcdFx0Y29uc3QgZmlyc3RFbmFibGVkID0gc2VsZWN0T3B0aW9ucy5maW5kKCAob3B0KSA9PiAhb3B0LmRpc2FibGVkICk7XHJcblx0XHRcdGlmICggZmlyc3RFbmFibGVkICkge1xyXG5cdFx0XHRcdHNldFNjZW5hcmlvKCB7IHBsYW5fbWFudWFsOiBmaXJzdEVuYWJsZWQua2V5IH0gKTtcclxuXHRcdFx0fVxyXG5cdFx0fVxyXG5cdH0sIFsgc2VsZWN0T3B0aW9ucywgY3VycmVudFNjZW5hcmlvLnBsYW5fZmllbGQsIGN1cnJlbnRTY2VuYXJpby5wbGFuX21hbnVhbCwgc2V0U2NlbmFyaW8gXSApO1xyXG5cclxuXHRjb25zdCBoYW5kbGVSZWZyZXNoUGxhbnMgPSAoKSA9PiB7XHJcblx0XHRzZXRJc1JlZnJlc2hpbmcoIHRydWUgKTtcclxuXHRcdHNldE5vdGljZVRleHQoICcnICk7XHJcblx0XHRzZXROb3RpY2VTdGF0dXMoICcnICk7XHJcblxyXG5cdFx0ZmV0Y2hQbGFucyggeyBmb3JjZVJlZnJlc2g6IHRydWUgfSApXHJcblx0XHRcdC50aGVuKCAoKSA9PiB7XHJcblx0XHRcdFx0c2V0Tm90aWNlVGV4dCggc2NlbmFyaW9MYWJlbCggJ3BsYW5zX2ZldGNoZWRfc3VjY2Vzc2Z1bGx5JyApICk7XHJcblx0XHRcdFx0c2V0Tm90aWNlU3RhdHVzKCAnc3VjY2VzcycgKTtcclxuXHRcdFx0fSApXHJcblx0XHRcdC5jYXRjaCggKCBlcnJvciApID0+IHtcclxuXHRcdFx0XHRjb25zdCBtc2cgPSBlcnJvcj8ubWVzc2FnZSB8fCAnUmVxdWVzdCBmYWlsZWQnO1xyXG5cdFx0XHRcdHNldE5vdGljZVRleHQoIG1zZyApO1xyXG5cdFx0XHRcdHNldE5vdGljZVN0YXR1cyggJ2Vycm9yJyApO1xyXG5cdFx0XHR9IClcclxuXHRcdFx0LmZpbmFsbHkoICgpID0+IHtcclxuXHRcdFx0XHRzZXRJc1JlZnJlc2hpbmcoIGZhbHNlICk7XHJcblx0XHRcdH0gKTtcclxuXHR9O1xyXG5cclxuXHRyZXR1cm4gKFxyXG5cdFx0PD5cclxuXHRcdFx0PFNlbGVjdENvbnRyb2xcclxuXHRcdFx0XHRsYWJlbD17IHNjZW5hcmlvTGFiZWwoICdzdWJzY3JpYmVfcGxhbl9maWVsZCcgKSB9XHJcblx0XHRcdFx0a2V5PXsgJ2Zvcm1fZmllbGRzX3N1YnNjcmliZV9wbGFuX2ZpZWxkJyB9XHJcblx0XHRcdFx0dmFsdWU9eyBjdXJyZW50U2NlbmFyaW8ucGxhbl9maWVsZCA/PyAnJyB9XHJcblx0XHRcdFx0bGFiZWxQb3NpdGlvbj1cInNpZGVcIlxyXG5cdFx0XHRcdG9uQ2hhbmdlPXsgKCBwbGFuX2ZpZWxkICkgPT4gc2V0U2NlbmFyaW8oIHsgcGxhbl9maWVsZCB9ICkgfVxyXG5cdFx0XHRcdG9wdGlvbnM9eyBmb3JtRmllbGRzIH1cclxuXHRcdFx0Lz5cclxuXHJcblx0XHRcdHsgISBjdXJyZW50U2NlbmFyaW8ucGxhbl9maWVsZCAmJiAoXHJcblx0XHRcdFx0PD5cclxuXHRcdFx0XHRcdDxCYXNlQ29udHJvbFxyXG5cdFx0XHRcdFx0XHRsYWJlbD17IHNjZW5hcmlvTGFiZWwoICdzdWJzY3JpYmVfcGxhbicgKSB9XHJcblx0XHRcdFx0XHQ+XHJcblx0XHRcdFx0XHRcdDxDdXN0b21TZWxlY3RDb250cm9sXHJcblx0XHRcdFx0XHRcdFx0aGlkZUxhYmVsRnJvbVZpc2lvblxyXG5cdFx0XHRcdFx0XHRcdG9wdGlvbnM9eyBzZWxlY3RPcHRpb25zIH1cclxuXHRcdFx0XHRcdFx0XHR2YWx1ZT17IGN1cnJlbnRQbGFuIH1cclxuXHRcdFx0XHRcdFx0XHRvbkNoYW5nZT17ICggeyBzZWxlY3RlZEl0ZW0gfSApID0+IHtcclxuXHRcdFx0XHRcdFx0XHRcdGlmICggc2VsZWN0ZWRJdGVtPy5kaXNhYmxlZCApIHsgcmV0dXJuOyB9XHJcblx0XHRcdFx0XHRcdFx0XHRzZXRTY2VuYXJpbyggeyBwbGFuX21hbnVhbDogc2VsZWN0ZWRJdGVtLmtleSB9ICk7XHJcblx0XHRcdFx0XHRcdFx0fSB9XHJcblx0XHRcdFx0XHRcdC8+XHJcblx0XHRcdFx0XHQ8L0Jhc2VDb250cm9sPlxyXG5cclxuXHRcdFx0XHRcdDxCdXR0b25cclxuXHRcdFx0XHRcdFx0aXNTZWNvbmRhcnlcclxuXHRcdFx0XHRcdFx0aXNCdXN5PXsgaXNSZWZyZXNoaW5nIH1cclxuXHRcdFx0XHRcdFx0ZGlzYWJsZWQ9eyBpc1JlZnJlc2hpbmcgfVxyXG5cdFx0XHRcdFx0XHRvbkNsaWNrPXsgaGFuZGxlUmVmcmVzaFBsYW5zIH1cclxuXHRcdFx0XHRcdD5cclxuXHRcdFx0XHRcdFx0eyBzY2VuYXJpb0xhYmVsKCAncmVmcmVzaF9wbGFuc19idXR0b24nICkgfVxyXG5cdFx0XHRcdFx0PC9CdXR0b24+XHJcblxyXG5cdFx0XHRcdFx0eyBub3RpY2VUZXh0ICYmIChcclxuXHRcdFx0XHRcdFx0PGRpdlxyXG5cdFx0XHRcdFx0XHRcdHN0eWxlPXsge1xyXG5cdFx0XHRcdFx0XHRcdFx0Y29sb3I6IG5vdGljZVN0YXR1cyA9PT0gJ3N1Y2Nlc3MnID8gJ2dyZWVuJyA6ICdyZWQnLFxyXG5cdFx0XHRcdFx0XHRcdFx0bWFyZ2luVG9wOiAnMC41ZW0nLFxyXG5cdFx0XHRcdFx0XHRcdFx0Zm9udFNpemU6ICcxM3B4JyxcclxuXHRcdFx0XHRcdFx0XHR9IH1cclxuXHRcdFx0XHRcdFx0PlxyXG5cdFx0XHRcdFx0XHRcdHsgbm90aWNlVGV4dCB9XHJcblx0XHRcdFx0XHRcdDwvZGl2PlxyXG5cdFx0XHRcdFx0KSB9XHJcblx0XHRcdFx0PC8+XHJcblx0XHRcdCkgfVxyXG5cdFx0PC8+XHJcblx0KTtcclxufVxyXG5cclxuZXhwb3J0IGRlZmF1bHQgY29tcG9zZShcclxuXHR3aXRoU2VsZWN0KCAoIC4uLnByb3BzICkgPT4gKCB7XHJcblx0XHQuLi53aXRoU2VsZWN0Rm9ybUZpZWxkcyggW10sIF9fKCAnTWFudWFsIElucHV0JywgJ2pldC1mb3JtLWJ1aWxkZXInICkgKSggLi4ucHJvcHMgKSxcclxuXHRcdC4uLndpdGhTZWxlY3RHYXRld2F5cyggLi4ucHJvcHMgKSxcclxuXHR9ICkgKSxcclxuXHR3aXRoRGlzcGF0Y2goICggLi4ucHJvcHMgKSA9PiAoIHtcclxuXHRcdC4uLndpdGhEaXNwYXRjaEdhdGV3YXlzKCAuLi5wcm9wcyApLFxyXG5cdH0gKSApLFxyXG4pKCBTdHJpcGVTdWJzY3JpcHRpb25TY2VuYXJpbyApO1xyXG4iXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOzs7Ozs7Ozs7Ozs7O0FDbEZBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUdBO0FBSUE7QUFFQTtBQUVBO0FBS0E7QUFNQTtBQU1BO0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBRUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7Ozs7Ozs7Ozs7OztBQy9DQTtBQUFBO0FBRUE7QUFDQTtBQUNBO0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUdBO0FBSUE7QUFDQTtBQUdBO0FBQ0E7QUFDQTtBQUdBO0FBV0E7QUFUQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFHQTtBQUNBO0FBR0E7QUFDQTtBQUFBO0FBQUE7QUFDQTtBQUVBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFFQTtBQUdBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUdBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUFBO0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQUE7QUFHQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFBQTtBQUVBO0FBQUE7QUFFQTtBQUVBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDdkZBO0FBRUE7QUFDQTtBQUNBO0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFHQTtBQUFBO0FBRUE7QUFZQTtBQVZBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBR0E7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFFQTtBQUVBO0FBQUE7QUFFQTtBQUFBO0FBRUE7QUFBQTtBQUtBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFFQTtBQUFBO0FBTUE7QUFDQTtBQUNBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFBQTtBQUlBO0FBRUE7QUFDQTtBQUdBO0FBR0E7QUFFQTs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQ25HQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQURBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUVBO0FBYUE7QUFBQTtBQVhBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUVBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUFBO0FBUkE7QUFVQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFiQTtBQUFBO0FBQUE7QUFlQTtBQUNBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFBQTtBQUNBO0FBRUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBRUE7QUFDQTtBQUtBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUFBO0FBQUE7QUFFQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUVBO0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQU1BO0FBQUE7QUFHQTtBQUNBO0FBQ0E7QUFDQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQUtBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFPQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFTQTtBQUVBO0FBQ0E7QUFFQTtBQUVBO0FBQ0E7Ozs7QSIsInNvdXJjZVJvb3QiOiIifQ==