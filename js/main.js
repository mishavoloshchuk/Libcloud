// Анімація
Element.prototype.show = function (){
	if (this.animState !== "hidden" && this.animState !== undefined) return;
	const animDuration = this.getAttribute('animationDuration');
	this.setAttribute('animState', 'showing');
	this.animState = 'showing';
	clearTimeout(this.timeout);
	this.timeout = setTimeout(() => {this.setAttribute('animState', 'showed'); this.animState = 'showed';}, animDuration);
}
Element.prototype.hide = function (){
	if (this.animState !== "showed" && this.animState !== undefined) return;
	const animDuration = this.getAttribute('animationDuration');
	this.setAttribute('animState', 'hiding');
	this.animState = 'hiding';
	clearTimeout(this.timeout);
	this.timeout = setTimeout(() => {this.setAttribute('animState', 'hidden'); this.animState = 'hidden'}, animDuration);
}

function getBodyScrollWidth() {
	return innerWidth - document.body.offsetWidth;
}

let unlockScrollTimeout;
function lockScrolling(){
	clearTimeout(unlockScrollTimeout);
	document.body.style.cssText = `overflow-y: hidden; padding-right: ${getBodyScrollWidth()}px;`;
}
function unlockScrolling(){
	document.body.style.cssText = 'overflow-y: auto; padding-right: 0;';
}

function showSideMenu (menuId){
	const sideMenuElem = document.getElementById(menuId);
	lockScrolling();
	sideMenuElem.show();
}
function hideSideMenu (menuId){
	const sideMenuElem = document.getElementById(menuId);
	if (sideMenuElem.getAttribute('animstate') == 'showed'){
		sideMenuElem.hide();
		unlockScrollTimeout = setTimeout(unlockScrolling, +sideMenuElem.getAttribute('animationDuration'));	
	}
}