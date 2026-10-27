const assert = require( 'node:assert/strict' );
const { readFileSync } = require( 'node:fs' );
const { resolve } = require( 'node:path' );
const { test } = require( 'node:test' );
const { JSDOM } = require( 'jsdom' );

const bundle = readFileSync(
	resolve( __dirname, '../../dist/js/app.js' ),
	'utf8'
);

function render( t, markup ) {
	const dom = new JSDOM( markup, { runScripts: 'outside-only' } );
	t.after( () => dom.window.close() );
	return dom.window;
}

function start( window ) {
	window.eval( bundle );
	window.document.dispatchEvent( new window.Event( 'DOMContentLoaded' ) );
}

function setMenuLayout( menu, layout ) {
	Object.defineProperty( menu, 'offsetWidth', {
		get: () => layout.width,
	} );
	menu.getBoundingClientRect = () => ( {
		left: layout.left + ( parseFloat( menu.style.left ) || 0 ),
	} );
}

const menuMarkup =
	'<header class="l-header"><ul><li class="snow-monkey-mega-menu"><ul class="c-navbar__submenu"></ul></li></ul></header>';

test( 'centers each built menu independently within the container', ( t ) => {
	const window = render(
		t,
		`<div class="l-container">${ menuMarkup }${ menuMarkup }</div>`
	);
	Object.defineProperty(
		window.document.querySelector( '.l-container' ),
		'offsetWidth',
		{ value: 1200 }
	);
	const menus = window.document.querySelectorAll( '.c-navbar__submenu' );
	setMenuLayout( menus[ 0 ], { width: 800, left: 100 } );
	setMenuLayout( menus[ 1 ], { width: 600, left: 400 } );
	menus[ 0 ].style.left = '350px';
	start( window );
	assert.equal( menus[ 0 ].style.left, '100px' );
	assert.equal( menus[ 1 ].style.left, '-100px' );
} );

test( 'recalculates after load and desktop or mobile viewport changes', ( t ) => {
	const window = render(
		t,
		`<div class="l-container">${ menuMarkup }</div>`
	);
	let containerWidth = 1200;
	Object.defineProperty(
		window.document.querySelector( '.l-container' ),
		'offsetWidth',
		{ get: () => containerWidth }
	);
	const menu = window.document.querySelector( '.c-navbar__submenu' );
	const layout = { width: 800, left: 100 };
	setMenuLayout( menu, layout );
	start( window );
	assert.equal( menu.style.left, '100px' );

	layout.left = 200;
	window.dispatchEvent( new window.Event( 'load' ) );
	assert.equal( menu.style.left, '0px' );

	containerWidth = 390;
	layout.width = 390;
	layout.left = 16;
	window.dispatchEvent( new window.Event( 'resize' ) );
	assert.equal( menu.style.left, '-16px' );
	window.dispatchEvent( new window.Event( 'resize' ) );
	assert.equal( menu.style.left, '-16px' );
} );

test( 'leaves left headers and ordinary menus untouched', ( t ) => {
	const window = render(
		t,
		`<div class="l-container">${ menuMarkup.replace(
			'l-header',
			'l-header l-header--left'
		) }<header class="l-header"><ul class="c-navbar__submenu"></ul></header></div>`
	);
	const menus = window.document.querySelectorAll( '.c-navbar__submenu' );
	for ( const menu of menus ) {
		menu.style.left = '42px';
	}
	start( window );
	window.dispatchEvent( new window.Event( 'resize' ) );
	for ( const menu of menus ) {
		assert.equal( menu.style.left, '42px' );
	}
} );

test( 'leaves nested submenu positions untouched', ( t ) => {
	const window = render(
		t,
		`<div class="l-container">${ menuMarkup.replace(
			'<ul class="c-navbar__submenu"></ul>',
			'<ul class="c-navbar__submenu"><li><ul class="c-navbar__submenu" style="left: 42px"></ul></li></ul>'
		) }</div>`
	);
	Object.defineProperty(
		window.document.querySelector( '.l-container' ),
		'offsetWidth',
		{ value: 1200 }
	);
	const menus = window.document.querySelectorAll( '.c-navbar__submenu' );
	setMenuLayout( menus[ 0 ], { width: 800, left: 100 } );
	start( window );
	assert.equal( menus[ 0 ].style.left, '100px' );
	assert.equal( menus[ 1 ].style.left, '42px' );
} );

test( 'handles pages without a container or without mega menus', ( t ) => {
	for ( const markup of [ menuMarkup, '<div class="l-container"></div>' ] ) {
		const window = render( t, markup );
		assert.doesNotThrow( () => start( window ) );
		window.dispatchEvent( new window.Event( 'resize' ) );
		const menu = window.document.querySelector( '.c-navbar__submenu' );
		if ( menu ) {
			assert.equal( menu.style.left, '' );
		}
	}
} );
