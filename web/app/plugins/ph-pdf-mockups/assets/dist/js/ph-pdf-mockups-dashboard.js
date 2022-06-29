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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
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
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 208);
/******/ })
/************************************************************************/
/******/ ({

/***/ 208:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(209);


/***/ }),

/***/ 209:
/***/ (function(module, __webpack_exports__) {

"use strict";
throw new Error("Module build failed: SyntaxError: /Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/assets/src/js/dashboard.js: Support for the experimental syntax 'optionalChaining' isn't currently enabled (24:14):\n\n\u001b[0m \u001b[90m 22 | \u001b[39m    async renderPdf() {\u001b[0m\n\u001b[0m \u001b[90m 23 | \u001b[39m      \u001b[36mif\u001b[39m (\u001b[0m\n\u001b[0m\u001b[31m\u001b[1m>\u001b[22m\u001b[39m\u001b[90m 24 | \u001b[39m        \u001b[33m!\u001b[39m\u001b[36mthis\u001b[39m\u001b[33m?\u001b[39m\u001b[33m.\u001b[39mimage\u001b[33m?\u001b[39m\u001b[33m.\u001b[39mattached_media \u001b[33m&&\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m    | \u001b[39m             \u001b[31m\u001b[1m^\u001b[22m\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 25 | \u001b[39m        \u001b[36mthis\u001b[39m\u001b[33m?\u001b[39m\u001b[33m.\u001b[39mimage\u001b[33m?\u001b[39m\u001b[33m.\u001b[39m_links\u001b[33m?\u001b[39m\u001b[33m.\u001b[39mself\u001b[33m?\u001b[39m\u001b[33m.\u001b[39m[\u001b[35m0\u001b[39m]\u001b[33m?\u001b[39m\u001b[33m.\u001b[39mhref\u001b[0m\n\u001b[0m \u001b[90m 26 | \u001b[39m      ) {\u001b[0m\n\u001b[0m \u001b[90m 27 | \u001b[39m        let [image] \u001b[33m=\u001b[39m await \u001b[36mthis\u001b[39m\u001b[33m.\u001b[39m$http\u001b[33m.\u001b[39mget(\u001b[0m\n\nAdd @babel/plugin-proposal-optional-chaining (https://git.io/vb4Sk) to the 'plugins' section of your Babel config to enable transformation.\n    at Parser.raise (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:4028:15)\n    at Parser.expectPlugin (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5364:18)\n    at Parser.parseSubscript (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6116:12)\n    at Parser.parseSubscripts (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6101:19)\n    at Parser.parseExprSubscripts (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6091:17)\n    at Parser.parseMaybeUnary (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6060:21)\n    at Parser.parseMaybeUnary (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6037:30)\n    at Parser.parseExprOps (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5945:21)\n    at Parser.parseMaybeConditional (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5917:21)\n    at Parser.parseMaybeAssign (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5864:21)\n    at Parser.parseExpression (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5817:21)\n    at Parser.parseParenExpression (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6623:20)\n    at Parser.parseIfStatement (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:7820:22)\n    at Parser.parseStatementContent (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:7509:21)\n    at Parser.parseStatement (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:7478:17)\n    at Parser.parseBlockOrModuleBlockBody (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:8046:23)\n    at Parser.parseBlockBody (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:8033:10)\n    at Parser.parseBlock (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:8022:10)\n    at Parser.parseFunctionBody (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:7130:24)\n    at Parser.parseFunctionBodyAndFinish (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:7112:10)\n    at Parser.parseMethod (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:7054:10)\n    at Parser.parseObjectMethod (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6968:19)\n    at Parser.parseObjPropValue (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:7010:21)\n    at Parser.parseObj (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6921:12)\n    at Parser.parseExprAtom (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6464:21)\n    at Parser.parseExprSubscripts (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6081:21)\n    at Parser.parseMaybeUnary (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:6060:21)\n    at Parser.parseExprOps (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5945:21)\n    at Parser.parseMaybeConditional (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5917:21)\n    at Parser.parseMaybeAssign (/Users/andre/Local Sites/projecthuddle-plugin/app/public/wp-content/plugins/ph-pdf-mockups/node_modules/@babel/parser/lib/index.js:5864:21)");

/***/ })

/******/ });