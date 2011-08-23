
// Make an editor!!
function SmfEditor(sSessionId, sUniqueId, bWysiwyg, sText, sEditWidth, sEditHeight, bRichEditOff)
{

	// Create some links to the editor object.
	this.sUniqueId = sUniqueId;
	this.oTextHandle = null;
	this.sCurrentText = typeof(sText) != 'undefined' ? sText : '';

	// How big?
	this.sEditWidth = typeof(sEditWidth) != 'undefined' ? sEditWidth : '70%';
	this.sEditHeight = typeof(sEditHeight) != 'undefined' ? sEditHeight : '150px';

	this.showDebug = false;
	this.bRichTextEnabled = typeof(bWysiwyg) != 'undefined' && bWysiwyg ? true : false;
	// This doesn't work on Opera as they cannot restore focus after clicking a BBC button.
	this.bRichTextPossible = ((is_ie5up && !is_ie50) || is_ff || is_opera95up || is_safari || is_chrome) && !bRichEditOff;

	this.oFrameHandle = null;
	this.oFrameDocument = null;
	this.oFrameWindow = null;

	// These hold the breadcrumb.
	this.oBreadHandle = null;
	this.oResizerElement = null;

	this.oSmileyPopupWindow = null;
	this.sCurSessionId = sSessionId;

	// Kinda holds all the useful stuff.
	this.aButtonControls = new Array();
	this.aSelectControls = new Array();
	this.aKeyboardShortcuts = new Array();
	this.oSmfSmileys = new Object;

	// This tracks the cursor position on IE to avoid refocus problems.
	this.cursorX = 0;
	this.cursorY = 0;

	// This is all the elements that can have a simple execCommand.
	this.oSimpleExec = {
		b: 'bold',
		u: 'underline',
		i: 'italic',
		s: 'strikethrough',
		left: 'justifyleft',
		center: 'justifycenter',
		right: 'justifyright',
		hr: 'inserthorizontalrule',
		list: 'insertunorderedlist',
		orderlist: 'insertorderedlist',
		sub: 'subscript',
		sup: 'superscript',
		indent: 'indent',
		outdent: 'outdent'
	}

	// Codes to call a private function
	this.oSmfExec = {
		unformat: 'removeFormatting',
		toggle: 'toggleView'
	}

	// Any special breadcrumb mappings to ensure we show a consistant tag name.
	this.breadCrumbNameTags = {
		strike: 's',
		strong: 'b',
		em: 'i'
	}

	this.aBreadCrumbNameStyles = [
		{
			sStyleType: 'text-decoration',
			sStyleValue: 'underline',
			sBbcTag: 'u'
		},
		{
			sStyleType: 'text-decoration',
			sStyleValue: 'line-through',
			sBbcTag: 's'
		},
		{
			sStyleType: 'text-align',
			sStyleValue: 'left',
			sBbcTag: 'left'
		},
		{
			sStyleType: 'text-align',
			sStyleValue: 'center',
			sBbcTag: 'center'
		},
		{
			sStyleType: 'text-align',
			sStyleValue: 'right',
			sBbcTag: 'right'
		},
		{
			sStyleType: 'font-weight',
			sStyleValue: 'bold',
			sBbcTag: 'b'
		},
		{
			sStyleType: 'font-style',
			sStyleValue: 'italic',
			sBbcTag: 'i'
		}
	];

	// All the fonts in the world.
	this.aFontFaces = [
		'Arial',
		'Arial Black',
		'Impact',
		'Verdana',
		'Times New Roman',
		'Georgia',
		'Andale Mono',
		'Trebuchet MS',
		'Comic Sans MS'
	];
	// Font maps (HTML => CSS size)
	this.aFontSizes = [
		0,
		8,
		10,
		12,
		14,
		18,
		24,
		36
	];
	// Color maps! (hex => name)
	this.oFontColors = {
		black: '#000000',
		red: '#ff0000',
		yellow: '#ffff00',
		pink: '#ffc0cb',
		green: '#008000',
		orange: '#ffa500',
		purple: '#800080',
		blue: '#0000ff',
		beige: '#f5f5dc',
		brown: '#a52a2a',
		teal: '#008080',
		navy: '#000080',
		maroon: '#800000',
		limegreen: '#32cd32'
	}

	this.sFormId = 'postmodify';
	this.iArrayPosition = smf_editorArray.length;

	// Current resize state.
	this.oSmfEditorCurrentResize = {};

	this.init();
}

SmfEditor.prototype.init = function()
{
	// Define the event wrapper functions.
	var oCaller = this;
	this.aEventWrappers = {
		editorKeyUp: function(oEvent) {return oCaller.editorKeyUp(oEvent);},
		shortcutCheck: function(oEvent) {return oCaller.shortcutCheck(oEvent);},
		editorBlur: function(oEvent) {return oCaller.editorBlur(oEvent);},
		editorFocus: function(oEvent) {return oCaller.editorFocus(oEvent);},
		startResize: function(oEvent) {return oCaller.startResize(oEvent);},
		resizeOverDocument: function(oEvent) {return oCaller.resizeOverDocument(oEvent);},
		endResize: function(oEvent) {return oCaller.endResize(oEvent);},
		resizeOverIframe: function(oEvent) {return oCaller.resizeOverIframe(oEvent);}
	};

	// Set the textHandle.
	this.oTextHandle = document.getElementById(this.sUniqueId);

	// Ensure the currentText is set correctly depending on the mode.
	if (this.sCurrentText == '' && !this.bRichTextEnabled)
		this.sCurrentText = smf_unhtmlspecialchars(getInnerHTML(this.oTextHandle));

	// Only try to do this if rich text is supported.
	if (this.bRichTextPossible)
	{
		// Make the iframe itself, stick it next to the current text area, and give it an ID.
		this.oFrameHandle = document.createElement('iframe');
		this.oFrameHandle.src = 'about:blank';
		this.oFrameHandle.id = 'html_' + this.sUniqueId;
		this.oFrameHandle.className = 'rich_editor_frame';
		this.oFrameHandle.style.display = 'none';
		this.oFrameHandle.tabIndex = this.oTextHandle.tabIndex;
		this.oTextHandle.parentNode.appendChild(this.oFrameHandle);


		// Create some handy shortcuts.
		this.oFrameDocument = this.oFrameHandle.contentDocument ? this.oFrameHandle.contentDocument : (typeof(this.oFrameHandle.contentWindow) == 'undefined' ? this.oFrameHandle.document : this.oFrameHandle.contentWindow.document);
		this.oFrameWindow = typeof(this.oFrameHandle.contentWindow) == 'undefined' ? this.oFrameHandle.document.parentWindow : this.oFrameHandle.contentWindow;


		// Create the debug window... and stick this under the main frame - make it invisible by default.
		this.oBreadHandle = document.createElement('div');
		this.oBreadHandle.id = 'bread_' . uid;
		this.oBreadHandle.style.visibility = 'visible';
		this.oBreadHandle.style.display = 'none';
		this.oFrameHandle.parentNode.appendChild(this.oBreadHandle);

		// Size the iframe dimensions to something sensible.
		this.oFrameHandle.style.width = this.sEditWidth;
		this.oFrameHandle.style.height = this.sEditHeight;
		this.oFrameHandle.style.visibility = 'visible';

		// Only bother formatting the debug window if debug is enabled.
		if (this.showDebug)
		{
			this.oBreadHandle.style.width = this.sEditWidth;
			this.oBreadHandle.style.height = '20px';
			this.oBreadHandle.className = 'windowbg2';
			this.oBreadHandle.style.border = '1px black solid';
			this.oBreadHandle.style.display = '';
		}

		// Populate the editor with nothing by default.
		if (!is_opera95up)
		{
			this.oFrameDocument.open();
			this.oFrameDocument.write('');
			this.oFrameDocument.close();
		}

		// Mark it as editable...
		if (this.oFrameDocument.body.contentEditable)
			this.oFrameDocument.body.contentEditable = true;
		else
		{
			this.oFrameHandle.style.display = '';
			this.oFrameDocument.designMode = 'on';
			this.oFrameHandle.style.display = 'none';
		}

		// Now we need to try and style the editor - internet explorer allows us to do the whole lot.
		if (document.styleSheets['editor_css'] || document.styleSheets['editor_ie_css'])
		{
			var oMyStyle = this.oFrameDocument.createElement('style');
			this.oFrameDocument.documentElement.firstChild.appendChild(oMyStyle);
			oMyStyle.styleSheet.cssText = document.styleSheets['editor_ie_css'] ? document.styleSheets['editor_ie_css'].cssText : document.styleSheets['editor_css'].cssText;
		}
		// Otherwise we seem to have to try to rip out each of the styles one by one!
		else if (document.styleSheets.length)
		{
			var bFoundSomething = false;
			// First we need to find the right style sheet.
			for (var i = 0, iNumStyleSheets = document.styleSheets.length; i < iNumStyleSheets; i++)
			{
				// Start off looking for the right style sheet.
				if (!document.styleSheets[i].href || document.styleSheets[i].href.indexOf('editor') < 1)
					continue;

				// Firefox won't allow us to get a CSS file which ain't in the right URL.
				try
				{
					if (document.styleSheets[i].cssRules.length < 1)
						continue;
				}
				catch (e)
				{
					continue;
				}

				// Manually try to find the rich_editor class.
				for (var r = 0, iNumRules = document.styleSheets[i].cssRules.length; r < iNumRules; r++)
				{
					// Got the main editor?
					if (document.styleSheets[i].cssRules[r].selectorText == '.rich_editor')
					{
						// Set some possible styles.
						if (document.styleSheets[i].cssRules[r].style.color)
							this.oFrameDocument.body.style.color = document.styleSheets[i].cssRules[r].style.color;
						if (document.styleSheets[i].cssRules[r].style.backgroundColor)
							this.oFrameDocument.body.style.backgroundColor = document.styleSheets[i].cssRules[r].style.backgroundColor;
						if (document.styleSheets[i].cssRules[r].style.fontSize)
							this.oFrameDocument.body.style.fontSize = document.styleSheets[i].cssRules[r].style.fontSize;
						if (document.styleSheets[i].cssRules[r].style.fontFamily)
							this.oFrameDocument.body.style.fontFamily = document.styleSheets[i].cssRules[r].style.fontFamily;
						if (document.styleSheets[i].cssRules[r].style.border)
							this.oFrameDocument.body.style.border = document.styleSheets[i].cssRules[r].style.border;
						bFoundSomething = true;
					}
					// The frame?
					else if (document.styleSheets[i].cssRules[r].selectorText == '.rich_editor_frame')
					{
						if (document.styleSheets[i].cssRules[r].style.border)
							this.oFrameHandle.style.border = document.styleSheets[i].cssRules[r].style.border;
					}
				}
			}

			// Didn't find it?
			if (!bFoundSomething)
			{
				// Do something that is better than nothing.
				this.oFrameDocument.body.style.color = 'black';
				this.oFrameDocument.body.style.backgroundColor = 'white';
				this.oFrameDocument.body.style.fontSize = 'small';
				this.oFrameDocument.body.style.fontFamily = 'verdana';
				this.oFrameDocument.body.style.border = 'none';
				this.oFrameHandle.style.border = '#808080';
			}
		}

		// Apply the class...
		this.oFrameDocument.body.className = 'rich_editor';

		// Listen for input.
		this.oFrameDocument.instanceRef = this;
		this.oFrameHandle.instanceRef = this;
		this.oTextHandle.instanceRef = this;

		// Attach addEventListener for those browsers that don't support it.
		createEventListener(this.oFrameHandle);
		createEventListener(this.oFrameDocument);
		createEventListener(this.oTextHandle);
		createEventListener(window);
		createEventListener(document);

		// Attach functions to the key and mouse events.
		this.oFrameDocument.addEventListener('keyup', this.aEventWrappers.editorKeyUp, true);
		this.oFrameDocument.addEventListener('mouseup', this.aEventWrappers.editorKeyUp, true);
		this.oFrameDocument.addEventListener('keydown', this.aEventWrappers.shortcutCheck, true);
		this.oTextHandle.addEventListener('keydown', this.aEventWrappers.shortcutCheck, true);

		if (is_ie)
		{
			this.oFrameDocument.addEventListener('blur', this.aEventWrappers.editorBlur, true);
			this.oFrameDocument.addEventListener('focus', this.aEventWrappers.editorFocus, true);
		}

		// Show the iframe only if wysiwyrg is on - and hide the text area.
		this.oTextHandle.style.display = this.bRichTextEnabled ? 'none' : '';
		this.oFrameHandle.style.display = this.bRichTextEnabled ? '' : 'none';
		this.oBreadHandle.style.display = this.bRichTextEnabled ? '' : 'none';
	}
	// If we can't do advanced stuff then just do the basics.
	else
	{
		// Cannot have WYSIWYG anyway!
		this.bRichTextEnabled = false;
	}

	// Make sure we set the message mode correctly.
	document.getElementById(this.sUniqueId + '_mode').value = this.bRichTextEnabled ? 1 : 0;

	// Show the resizer.
	if (document.getElementById(this.sUniqueId + '_resizer') && (!is_opera || is_opera95up) && !(is_chrome && !this.bRichTextEnabled))
	{
		// Currently nothing is being resized...I assume!
		window.smf_oCurrentResizeEditor = null;

		this.oResizerElement = document.getElementById(this.sUniqueId + '_resizer');
		this.oResizerElement.style.display = '';

		createEventListener(this.oResizerElement);
		this.oResizerElement.addEventListener('mousedown', this.aEventWrappers.startResize, false);
	}

	// Set the text - if WYSIWYG is enabled that is.
	if (this.bRichTextEnabled)
	{
		this.insertText(this.sCurrentText, true);

		// Better make us the focus!
		this.setFocus();
	}

	// And add the select controls.
	for (i in this.aSelectControls)
		this.addSelect(this.aSelectControls[i]);

	// Finally, register shortcuts.
	this.registerDefaultShortcuts();
}

// Return the current text.
SmfEditor.prototype.getText = function(bPrepareEntities, bModeOverride)
{
	var bCurMode = typeof(bModeOverride) != 'undefined' ? bModeOverride : this.bRichTextEnabled;

	if (!bCurMode || this.oFrameDocument == null)
	{
		var sText = this.oTextHandle.value;
		if (bPrepareEntities)
			sText = sText.replace(/</g, '#smlt#').replace(/>/g, '#smgt#').replace(/&/g, '#smamp#');
	}
	else
	{
		var sText = this.oFrameDocument.body.innerHTML;
		if (bPrepareEntities)
			sText = sText.replace(/&lt;/g, '#smlt#').replace(/&gt;/g, '#smgt#').replace(/&amp;/g, '#smamp#');
	}

	// Clean it up - including removing semi-colons.
	if (bPrepareEntities)
		sText = sText.replace(/&nbsp;/g, ' ').replace(/;/g, '#smcol#');

	// Return it.
	return sText;
}

// Return the current text.
SmfEditor.prototype.unprotectText = function(sText)
{
	var bCurMode = typeof(bModeOverride) != 'undefined' ? bModeOverride : this.bRichTextEnabled;

	// This restores smlt, smgt and smamp into boring entities, to unprotect against XML'd information like quotes.
	sText = sText.replace(/#smlt#/g, '&lt;').replace(/#smgt#/g, '&gt;').replace(/#smamp#/g, '&amp;');

	// Return it.
	return sText;
}

SmfEditor.prototype.editorKeyUp = function()
{
	// Rebuild the breadcrumb.
	this.updateEditorControls();
}

SmfEditor.prototype.editorBlur = function()
{
	if (!is_ie)
		return;

	// Need to do something here.
}

SmfEditor.prototype.editorFocus = function()
{
	if (!is_ie)
		return;

	// Need to do something here.
}

// Rebuild the breadcrumb etc - and set things to the correct context.
SmfEditor.prototype.updateEditorControls = function()
{
	// Assume nothing.
	if (typeof(this.aSelectControls.face) != 'undefined')
		this.aSelectControls.face.value = '';
	if (typeof(this.aSelectControls.size) != 'undefined')
		this.aSelectControls.size.value = '';
	if (typeof(this.aSelectControls.color) != 'undefined')
		this.aSelectControls.color.value = '';

	// Everything else is specific to HTML mode.
	if (!this.bRichTextEnabled)
		return;

	var aCrumb = new Array();
	var aAllCrumbs = new Array();
	var iMaxLength = 6;

	// What is the current element?
	var oCurTag = this.getCurElement();

	var i = 0;
	while (typeof(oCurTag) == 'object' && oCurTag != null && oCurTag.nodeName.toLowerCase() != 'body' && i < iMaxLength)
	{
		aCrumb[i++] = oCurTag;
		oCurTag = oCurTag.parentNode;
	}

	// Now print out the tree.
	var sTree = '';
	var sCurFontName = '';
	var sCurFontSize = '';
	var sCurFontColor = '';
	for (var i = 0, iNumCrumbs = aCrumb.length; i < iNumCrumbs; i++)
	{
		var sCrumbName = aCrumb[i].nodeName.toLowerCase();

		// Does it have an alternative name?
		if (typeof(this.breadCrumbNameTags[sCrumbName]) != 'undefined')
			sCrumbName = this.breadCrumbNameTags[sCrumbName];
		// Don't bother with this...
		else if (sCrumbName == 'p')
			continue;
		// A link?
		else if (sCrumbName == 'a')
		{
			var sUrlInfo = aCrumb[i].getAttribute('href');
			sCrumbName = 'url';
			if (typeof(sUrlInfo) == 'string')
			{
				if (sUrlInfo.substr(0, 3) == 'ftp')
					sCrumbName = 'ftp';
				else if (sUrlInfo.substr(0, 6) == 'mailto')
					sCrumbName = 'email';
			}
		}
		else if (sCrumbName == 'span' || sCrumbName == 'div')
		{
			if (aCrumb[i].style)
			{
				for (var j = 0, iNumStyles = this.aBreadCrumbNameStyles.length; j < iNumStyles; j++)
				{
					// Do we have a font?
					if (aCrumb[i].style.fontFamily && aCrumb[i].style.fontFamily != '' && sCurFontName == '')
					{
						sCurFontName = aCrumb[i].style.fontFamily;
						sCrumbName = 'face';
					}
					// ... or a font size?
					if (aCrumb[i].style.fontSize && aCrumb[i].style.fontSize != '' && sCurFontSize == '')
					{
						sCurFontSize = aCrumb[i].style.fontSize;
						sCrumbName = 'size';
					}
					// ... even color?
					if (aCrumb[i].style.color && aCrumb[i].style.color != '' && sCurFontColor == '')
					{
						sCurFontColor = aCrumb[i].style.color;
						if (in_array(sCurFontColor, this.oFontColors))
							sCurFontColor = array_search(sCurFontColor, this.oFontColors);
						sCrumbName = 'color';
					}

					if (this.aBreadCrumbNameStyles[j].sStyleType == 'text-align' && aCrumb[i].style.textAlign && aCrumb[i].style.textAlign == this.aBreadCrumbNameStyles[j].sStyleValue)
						sCrumbName = this.aBreadCrumbNameStyles[j].sBbcTag;
					else if (this.aBreadCrumbNameStyles[j].sStyleType == 'text-decoration' && aCrumb[i].style.textDecoration && aCrumb[i].style.textDecoration == this.aBreadCrumbNameStyles[j].sStyleValue)
						sCrumbName = this.aBreadCrumbNameStyles[j].sBbcTag;
					else if (this.aBreadCrumbNameStyles[j].sStyleType == 'font-weight' && aCrumb[i].style.fontWeight && aCrumb[i].style.fontWeight == this.aBreadCrumbNameStyles[j].sStyleValue)
						sCrumbName = this.aBreadCrumbNameStyles[j].sBbcTag;
					else if (this.aBreadCrumbNameStyles[j].sStyleType == 'font-style' && aCrumb[i].style.fontStyle && aCrumb[i].style.fontStyle == this.aBreadCrumbNameStyles[j].sStyleValue)
						sCrumbName = this.aBreadCrumbNameStyles[j].sBbcTag;
				}
			}
		}
		// Do we have a font?
		else if (sCrumbName == 'font')
		{
			if (aCrumb[i].getAttribute('face') && sCurFontName == '')
			{
				sCurFontName = aCrumb[i].getAttribute('face').toLowerCase();
				sCrumbName = 'face';
			}
			if (aCrumb[i].getAttribute('size') && sCurFontSize == '')
			{
				sCurFontSize = aCrumb[i].getAttribute('size');
				sCrumbName = 'size';
			}
			if (aCrumb[i].getAttribute('color') && sCurFontColor == '')
			{
				sCurFontColor = aCrumb[i].getAttribute('color');
				if (in_array(sCurFontColor, this.oFontColors))
					sCurFontColor = array_search(sCurFontColor, this.oFontColors);
				sCrumbName = 'color';
			}
			// Something else - ignore.
			if (sCrumbName == 'font')
				continue;
		}

		sTree += (i != 0 ? '&nbsp;<b>&gt;</b>' : '') + '&nbsp;' + sCrumbName;
		aAllCrumbs[aAllCrumbs.length] = sCrumbName;
	}

	for (i in this.aButtonControls)
	{
		var bNewState = in_array(this.aButtonControls[i].sCode, aAllCrumbs);
		if (bNewState != this.aButtonControls[i].bIsActive)
		{
			this.aButtonControls[i].bIsActive = bNewState;
			this.aButtonControls[i].oCodeHandle.style.backgroundImage = 'url(' + smf_images_url + (bNewState ? '/bbc/bbc_hoverbg.gif' : '/bbc/bbc_bg.gif') + ')';
		}
	}

	// Try set the font boxes correct.
	if (typeof(this.aSelectControls.face) == 'object')
		this.aSelectControls.face.value = sCurFontName ;
	if (typeof(this.aSelectControls.size) == 'object')
		this.aSelectControls.size.value = sCurFontSize ;
	if (typeof(this.aSelectControls.color) == 'object')
		this.aSelectControls.color.value = sCurFontColor ;

	if (this.showDebug)
		setInnerHTML(this.oBreadHandle, sTree);
}

// Set the HTML content to be that of the text box - if we are in wysiwyg mode.
SmfEditor.prototype.doSubmit = function()
{
	if (this.bRichTextEnabled)
		this.oTextHandle.value = this.oFrameDocument.body.innerHTML;
}

// Populate the box with text.
SmfEditor.prototype.insertText = function(sText, bClear, bForceEntityReverse, iMoveCursorBack)
{
	if (bForceEntityReverse)
		sText = this.unprotectText(sText);

	// Erase it all?
	if (bClear)
	{
		if (this.bRichTextEnabled)
		{
			// This includes a work around for FF to get the cursor to show!
			this.oFrameDocument.body.innerHTML = sText;

			// If FF trick the cursor into coming back!
			if (is_ff)
			{
				// For some entirely unknown reason FF3 Beta 2 requires this.
				this.oFrameDocument.body.contentEditable = false;

				this.oFrameDocument.designMode = 'off';
				this.oFrameDocument.designMode = 'on';
			}
		}
		else
			this.oTextHandle.value = sText;
	}
	else
	{
		this.setFocus();
		if (this.bRichTextEnabled)
		{
			// IE croaks if you have an image selected and try to insert!
			if (typeof(this.oFrameDocument.selection) != 'undefined' && this.oFrameDocument.selection.type != 'Text' && this.oFrameDocument.selection.type != 'None' && this.oFrameDocument.selection.clear)
				this.oFrameDocument.selection.clear();

			var oRange = this.getRange();

			if (oRange.pasteHTML)
			{
				oRange.pasteHTML(sText);

				// Do we want to move the cursor back at all?
				if (iMoveCursorBack)
					oRange.moveEnd('character', -iMoveCursorBack);

				oRange.select();
			}
			else
			{
				// If the cursor needs to be positioned, insert the last fragment first.
				if (typeof(iMoveCursorBack) != 'undefined' && iMoveCursorBack > 0 && sText.length > iMoveCursorBack)
				{
					var oSelection = this.getSelect(false, false);
					var oRange = oSelection.getRangeAt(0);
					oRange.insertNode(this.oFrameDocument.createTextNode(sText.substr(sText.length - iMoveCursorBack)));
				}

				this.smf_execCommand('inserthtml', false, typeof(iMoveCursorBack) == 'undefined' ? sText : sText.substr(0, sText.length - iMoveCursorBack));

				// This is a git - we need to do all kinds of crap. Thanks to this page:
				// http://www.richercomponents.com/Forums/ShowPost.aspx?PostID=2777
				// Create a new span element first...
				/*var oElement = this.oFrameDocument.createElement('span');
				oElement.innerHTML = sText;

				var oSelection = this.getSelect();
				if (!oRange)
					oSelection.collapse(this.oFrameDocument.getElementsByTagName('body')[0].firstChild,0);

				oSelection.removeAllRanges();
				oRange.deleteContents();

				var oContainer = oRange.startContainer;
				var iPos = oRange.startOffset;
				oRange = this.oFrameDocument.createRange();

				if (oContainer.nodeType == 3)
				{
					var oTextNode = oContainer;
					oContainer = oTextNode.parentNode;
					sText = oTextNode.nodeValue;
					var sTextBefore = sText.substr(0, iPos);
					var sTextAfter = sText.substr(iPos);
					var oBeforeNode = this.oFrameDocument.createTextNode(sTextBefore);
					var oAfterNode = this.oFrameDocument.createTextNode(sTextAfter);
					oContainer.insertBefore(oAfterNode, oTextNode);
					oContainer.insertBefore(oElement, oAfterNode);
					oContainer.insertBefore(oBeforeNode, oElement);
					oContainer.removeChild(oTextNode);

					//!!! Why does this not work on opera?
					if (!is_opera)
					{
						oRange.setEndBefore(oAfterNode);
						oRange.setStartBefore(oAfterNode);
					}
				}
				else
				{
					oContainer.insertBefore(oElement, oContainer.childNodes[iPos]);
					oRange.setEnd(oContainer, iPos + 1);
					oRange.setStart(oContainer, iPos + 1);
				}
				oSelection.addRange(oRange);*/
			}
		}
		else
		{
			replaceText(sText, this.oTextHandle);
		}
	}
}

// Add all the smileys into the "knowledge base" ;)
SmfEditor.prototype.addSmiley = function(sCode, sSmileyName, sDesc)
{
	if (this.oSmfSmileys[sCode])
		return;

	this.oSmfSmileys[sCode] = {
		sCode: sCode,
		sName: sSmileyName,
		sDescription: sDesc
	}

	var oSmileyHandle = document.getElementById('sml_' + sSmileyName);

	if (oSmileyHandle)
	{
		// Setup the event callback.
		oSmileyHandle.instanceRef = this;
		oSmileyHandle.onclick = function()
		{
			this.instanceRef.smileyEventHandler(this);
		};

		oSmileyHandle.code = sCode;
		oSmileyHandle.smileyname = sSmileyName;
		oSmileyHandle.desc = sDesc;
	}
}

SmfEditor.prototype.addButton = function(sCode, sBefore, sAfter)
{
	var oCodeHandle = document.getElementById('cmd_' + sCode);

	// If it don't exist we cannot create it!
	if (!oCodeHandle)
		return false;

	this.aButtonControls[sCode] = {
		oCodeHandle: oCodeHandle,
		sCode: sCode,
		sBefore: sBefore,
		sAfter: sAfter,
		bIsActive: false
	};

/*
	Array(4);
	this.aButtonControls[sCode][0] = oCodeHandle;
	this.aButtonControls[sCode][1] = sCode;
	this.aButtonControls[sCode][2] = sBefore;
	this.aButtonControls[sCode][3] = sAfter;
	// This holds whether or not it's active.
	this.aButtonControls[sCode][4] = false;
*/

	// Tie all the relevant actions to the event handler.
	oCodeHandle.instanceRef = this;
	oCodeHandle.onclick = function()
	{
		this.instanceRef.buttonEventHandler(this, 'click');
	}
	oCodeHandle.onmouseover = function()
	{
		this.instanceRef.buttonEventHandler(this, 'mouseover');
	}
	oCodeHandle.onmouseout = function()
	{
		this.instanceRef.buttonEventHandler(this, 'mouseout');
	}

	oCodeHandle.code = sCode;

	return true;
}

// Populate/handle the select boxes.
SmfEditor.prototype.addSelect = function(sSelectType)
{
	var oSelectHandle = document.getElementById('sel_' + sSelectType);
	if (typeof(oSelectHandle) != 'object' || !oSelectHandle)
		return;

	oSelectHandle.code = sSelectType;

	this.aSelectControls[sSelectType] = oSelectHandle;

	// Font face box!
	if (sSelectType == 'face')
	{
		// Add in the other options.
		for (var i = 0; i < this.aFontFaces.length; i++)
			oSelectHandle.options[oSelectHandle.options.length] = new Option(this.aFontFaces[i], this.aFontFaces[i].toLowerCase());
	}

	oSelectHandle.instanceRef = this;
	oSelectHandle.onchange = function()
	{
		this.instanceRef.selectEventHandler(this)
	}
}

SmfEditor.prototype.smileyEventHandler = function(oSrcElement)
{
	// Assume it does exist?!
	if (typeof(oSrcElement.code) == 'undefined')
		return false;

	this.insertSmiley(oSrcElement.smileyname, oSrcElement.code, oSrcElement.desc);
	return true;
}

// Special handler for WYSIWYG.
SmfEditor.prototype.smf_execCommand = function(sCommand, bUi, sValue)
{
	return this.oFrameDocument.execCommand(sCommand, bUi, sValue);
}

SmfEditor.prototype.insertSmiley = function(sName, sCode, sDesc)
{
	// In text mode we just add it in as we always did.
	if (!this.bRichTextEnabled)
		this.insertText(' ' + sCode);

	// Otherwise we need to do a whole image...
	else
	{
		var iUniqueSmileyId = 1000 + Math.floor(Math.random() * 100000);
		this.insertText('<img src="' + smf_smileys_url + '/' + sName + '" id="smiley_' + iUniqueSmileyId + '_' + sName + '" onresizestart="return false;" align="bottom" alt="" title="' + smf_htmlspecialchars(sDesc) + '" style="padding: 0 3px 0 3px;" />');
	}
}

SmfEditor.prototype.buttonEventHandler = function(oSrcElement, sEventType)
{
	if (typeof(oSrcElement.code) == 'undefined' || typeof(this.aButtonControls[oSrcElement.code]) == 'undefined')
		return false;

	// Are handling a hover?
	if (sEventType == 'mouseover' || sEventType == 'mouseout')
	{
		// Work out whether we should highlight it or not. On non-WYSWIYG we highlight on mouseover, on WYSIWYG we toggle current state.
		var bIsHighlight = sEventType == 'mouseover';
		if (this.bRichTextEnabled && this.aButtonControls[oSrcElement.code].bIsActive)
			bIsHighlight = !bIsHighlight;

		oSrcElement.style.backgroundImage = "url(" + smf_images_url + (bIsHighlight ? '/bbc/bbc_hoverbg.gif' : '/bbc/bbc_bg.gif') + ")";
	}
	else if (sEventType == 'click')
	{
		this.setFocus();

		// A special SMF function?
		if (this.oSmfExec[oSrcElement.code])
			this[this.oSmfExec[oSrcElement.code]]();

		else
		{
			// In text this is easy...
			if (!this.bRichTextEnabled)
			{
				// Replace?
				if (this.aButtonControls[oSrcElement.code].sAfter == '')
					replaceText(this.aButtonControls[oSrcElement.code].sBefore, this.oTextHandle)

				// Surround!
				else
					surroundText(this.aButtonControls[oSrcElement.code].sBefore, this.aButtonControls[oSrcElement.code].sAfter, this.oTextHandle)
			}
			else
			{
				// Is it easy?
				if (this.oSimpleExec[oSrcElement.code])
					this.smf_execCommand(this.oSimpleExec[oSrcElement.code], false, null);

				// A link?
				else if (oSrcElement.code == 'url' || oSrcElement.code == 'email' || oSrcElement.code == 'ftp')
					this.insertLink(oSrcElement.code);

				// Maybe an image?
				else if (oSrcElement.code == 'img')
					this.insertImage();

				// Everything else means doing something ourselves.
				else if (this.aButtonControls[oSrcElement.code].sBefore)
					this.insertCustomHTML(oSrcElement.code);

			}
		}

		// If this is WYSWIYG toggle this button state.
		if (this.bRichTextEnabled)
		{
			this.aButtonControls[oSrcElement.code].bIsActive = !this.aButtonControls[oSrcElement.code].bIsActive;
			oSrcElement.style.backgroundImage = "url(" + smf_images_url + (this.aButtonControls[oSrcElement.code].bIsActive ? '/bbc/bbc_hoverbg.gif' : '/bbc/bbc_bg.gif') + ")";
		}

		this.updateEditorControls();

		// Finally set the focus.
		this.setFocus();
	}

	return true;
}

// Changing a select box?
SmfEditor.prototype.selectEventHandler = function(oSrcElement)
{
	// Make sure it exists.
	if (!oSrcElement || !this.aSelectControls[oSrcElement.code])
		return false;

	this.setFocus();

	var sValue = document.getElementById('sel_' + oSrcElement.code).value;

	// Changing font face?
	if (oSrcElement.code == 'face')
	{
		// Not in HTML mode?
		if (!this.bRichTextEnabled)
		{
			sValue = sValue.replace(/"/, '');
			surroundText('[font=' + sValue + ']', '[/font]', this.oTextHandle)
		}
		else
			this.smf_execCommand('fontname', false, sValue);
	}

	// Font size?
	else if (oSrcElement.code == 'size')
	{
		// Are we in boring mode?
		if (!this.bRichTextEnabled)
			surroundText('[size=' + sValue + ']', '[/size]', this.oTextHandle)

		else
			this.smf_execCommand('fontsize', false, sValue);
	}
	// Or color even?
	else if (oSrcElement.code == 'color')
	{
		// Are we in boring mode?
		if (!this.bRichTextEnabled)
			surroundText('[color=' + sValue + ']', '[/color]', this.oTextHandle)

		else
			this.smf_execCommand('forecolor', false, sValue);
	}

	this.updateEditorControls();

	return true;
}

// Put in some custom HTML.
SmfEditor.prototype.insertCustomHTML = function(sCode)
{
	if (!this.aButtonControls[sCode])
		return;

	var sSelection = this.getSelect(true, true);
	if (sSelection.length == 0)
		sSelection = '';

	var sLeftTag = this.aButtonControls[sCode].sBefore;
	var sRightTag = this.aButtonControls[sCode].sAfter;

	// Are we overwriting?
	if (sRightTag == '')
		this.insertText(sLeftTag);
	// If something was selected, replace and position cursor at the end of it.
	else if (sSelection.length > 0)
		this.insertText(sLeftTag + sSelection + sRightTag, false, false, 0);
	// Wrap the tags around the cursor position.
	else
		this.insertText(sLeftTag + sRightTag, false, false, sRightTag.length);

}

// Insert a URL link.
SmfEditor.prototype.insertLink = function(sType)
{
	if (sType == 'email')
		var sPromptText = oEditorStrings['prompt_text_email'];
	else if (sType == 'ftp')
		var sPromptText = oEditorStrings['prompt_text_ftp'];
	else
		var sPromptText = oEditorStrings['prompt_text_url'];

	// IE has a nice prompt for this - others don't.
	if (sType != 'email' && sType != 'ftp' && is_ie)
		this.smf_execCommand('createlink', true, 'http://');

	else
	{
		// Ask them where to link to.
		var sText = prompt(sPromptText, sType == 'email' ? '' : (sType == 'ftp' ? 'ftp://' : 'http://'));
		if (!sText)
			return;

		if (sType == 'email' && sText.indexOf('mailto:') != 0)
			sText = 'mailto:' + sText;

		// Check if we have text selected and if not force us to have some.
		curText = this.getSelect(true);

		if (curText.toString().length != 0)
		{
			this.smf_execCommand('unlink');
			this.smf_execCommand('createlink', false, sText);
		}
		else
			this.insertText('<a href="' + sText + '">' + sText + '</a>');
	}
}

SmfEditor.prototype.insertImage = function(sSrc)
{
	if (!sSrc)
	{
		sSrc = prompt(oEditorStrings['prompt_text_img'], 'http://');
		if (!sSrc || sSrc.length < 10)
			return;
	}
	this.smf_execCommand('insertimage', false, sSrc);
}

SmfEditor.prototype.getSelect = function(bWantText, bWantHTMLText)
{
	if (is_ie && this.oFrameDocument.selection)
	{
		// Just want plain text?
		if (bWantText && !bWantHTMLText)
			return this.oFrameDocument.selection.createRange().text;
		// We want the HTML flavoured variety?
		else if (bWantHTMLText)
			return this.oFrameDocument.selection.createRange().htmlText;

		return this.oFrameDocument.selection;
	}

	// This is mainly Firefox.
	if (this.oFrameWindow.getSelection)
	{
		// Plain text?
		if (bWantText && !bWantHTMLText)
			return this.oFrameWindow.getSelection().toString();
		// HTML is harder - currently using: http://www.faqts.com/knowledge_base/view.phtml/aid/32427
		else if (bWantHTMLText)
		{
			var oSelection = this.oFrameWindow.getSelection();
			if (oSelection.rangeCount > 0)
			{
				var oRange = oSelection.getRangeAt(0);
				var oClonedSelection = oRange.cloneContents();
				var oDiv = this.oFrameDocument.createElement('div');
				oDiv.appendChild(oClonedSelection);
				return oDiv.innerHTML;
			}
			else
				return '';
		}

		// Want the whole object then.
		return this.oFrameWindow.getSelection();
	}

	// If we're here it's not good.
	return this.oFrameDocument.getSelection();
}

SmfEditor.prototype.getRange = function()
{
	// Get the current selection.
	var oSelection = this.getSelect();

	if (!oSelection)
		return null;

	if (is_ie && oSelection.createRange)
		return oSelection.createRange();

	return oSelection.rangeCount == 0 ? null : oSelection.getRangeAt(0);
}

// Get the current element.
SmfEditor.prototype.getCurElement = function()
{
	var oRange = this.getRange();

	if (!oRange)
		return null;

	if (is_ie)
	{
		if (oRange.item)
			return oRange.item(0);
		else
			return oRange.parentElement();
	}
	else
	{
		var oElement = oRange.commonAncestorContainer;
		return this.getParentElement(oElement);
	}
}

SmfEditor.prototype.getParentElement = function(oNode)
{
	if (oNode.nodeType == 1)
		return oNode;

	for (var i = 0; i < 50; i++)
	{
		if (!oNode.parentNode)
			break;

		oNode = oNode.parentNode;
		if (oNode.nodeType == 1)
			return oNode;
	}
	return null;
}

// Remove formatting for the selected text.
SmfEditor.prototype.removeFormatting = function()
{
	// Do both at once.
	if (this.bRichTextEnabled)
	{
		this.smf_execCommand('removeformat');
		this.smf_execCommand('unlink');
	}
	// Otherwise do a crude move indeed.
	else
	{
		// Get the current selection first.
		if (this.oTextHandle.caretPos)
		{
			var sCurrentText = this.oTextHandle.caretPos.text;
		}
		else if (typeof(this.oTextHandle.selectionStart) != "undefined")
		{
			var sCurrentText = this.oTextHandle.value.substr(this.oTextHandle.selectionStart, (this.oTextHandle.selectionEnd - this.oTextHandle.selectionStart));
		}
		else
			return;

		// Do bits that are likely to have attributes.
		sCurrentText = sCurrentText.replace(RegExp("\\[/?(url|img|iurl|ftp|email|img|color|font|size|list|bdo).*?\\]", "g"), '');
		// Then just anything that looks like BBC.
		sCurrentText = sCurrentText.replace(RegExp("\\[/?[A-Za-z]+\\]", "g"), '');

		replaceText(sCurrentText, this.oTextHandle);
	}
}

// Toggle wysiwyg/normal mode.
SmfEditor.prototype.toggleView = function(bView)
{
	if (!this.bRichTextPossible)
	{
		alert(oEditorStrings['wont_work']);
		return false;
	}

	// Overriding or alternating?
	if (typeof(bView) == 'undefined')
		bView = !this.bRichTextEnabled;

	this.requestParsedMessage(bView);

	return true;
}

// Request the message in a different form.
SmfEditor.prototype.requestParsedMessage = function(bView)
{
	// Replace with a force reload.
	if (!window.XMLHttpRequest)
	{
		alert(oEditorStrings['func_disabled']);
		return;
	}

	// Get the text.
	var sText = this.getText(true, !bView).replace(/&#/g, "&#38;#").php_to8bit().php_urlencode();

	this.tmpMethod = sendXMLDocument;
	this.tmpMethod(smf_prepareScriptUrl(smf_scripturl) + 'action=jseditor;view=' + (bView ? 1 : 0) + ';sesc=' + this.sCurSessionId + ';xml', 'message=' + sText, this.onToggleDataReceived);
	delete tmpMethod;
}

SmfEditor.prototype.onToggleDataReceived = function(oXMLDoc)
{
	var sText = '';
	for (var i = 0; i < oXMLDoc.getElementsByTagName('message')[0].childNodes.length; i++)
		sText += oXMLDoc.getElementsByTagName('message')[0].childNodes[i].nodeValue;

	// What is this new view we have?
	this.bRichTextEnabled = oXMLDoc.getElementsByTagName('message')[0].getAttribute('view') != '0';

	if (this.bRichTextEnabled)
	{
		this.oFrameHandle.style.display = '';
		this.oBreadHandle.style.display = '';
		this.oTextHandle.style.display = 'none';
	}
	else
	{
		sText = sText.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
		this.oFrameHandle.style.display = 'none';
		this.oBreadHandle.style.display = 'none';
		this.oTextHandle.style.display = '';

		// If we're leaving WYSIWYG all buttons need to be off.
		for (i in this.aButtonControls)
		{
			this.aButtonControls[i].bIsActive = false;
			this.aButtonControls[i].oCodeHandle.style.backgroundImage = "url(" + smf_images_url + '/bbc/bbc_bg.gif' + ")";
		}
	}

	this.insertText(sText, true);

	// Record the new status.
	document.getElementById(this.sUniqueId + '_mode').value = this.bRichTextEnabled ? 1 : 0;

	// Focus, focus, focus.
	this.setFocus();

	// Rebuild the bread crumb!
	this.updateEditorControls();
}

// Show the "More Smileys" popup box.
SmfEditor.prototype.showMoreSmileys = function(postbox, sTitleText, sPickText, sCloseText, smf_theme_url)
{
	if (this.oSmileyPopupWindow)
		this.oSmileyPopupWindow.close();

	this.oSmileyPopupWindow = window.open('', 'add_smileys', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes');
	this.oSmileyPopupWindow.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html>');
	this.oSmileyPopupWindow.document.write('\n\t<head>\n\t\t<title>' + sTitleText + '</title>\n\t\t<link rel="stylesheet" type="text/css" href="' + smf_theme_url + '/style.css" />\n\t</head>');
	this.oSmileyPopupWindow.document.write('\n\t<body style="margin: 1ex;">\n\t\t<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">\n\t\t\t<tr class="titlebg"><td align="left">' + sPickText + '</td></tr>\n\t\t\t<tr class="windowbg"><td align="left">');

	// Variable smileys is set in the template...for now.
	for (var iRow = 0; iRow < smileys.length; iRow++)
	{
		for (i = 0; i < smileys[iRow].length; i++)
		{
			smileys[iRow][i][2] = smileys[iRow][i][2].replace(/"/g, '&quot;');
			smileys[iRow][i][0] = smileys[iRow][i][0].replace(/"/g, '&quot;');
			this.oSmileyPopupWindow.document.write('<a href="javascript:void(0);" onclick="window.opener.editorHandle' + postbox + '.insertSmiley(\'' + smf_addslashes(smileys[iRow][i][1]) + '\', \'' + smf_addslashes(smileys[iRow][i][0]) + '\', \'' + smf_addslashes(smileys[iRow][i][2]) + '\'); window.focus(); return false;"><img src="' + smf_smileys_url + '/' + smileys[iRow][i][1] + '" id="sml_' + smileys[iRow][i][1] + '" alt="' + smileys[iRow][i][2] + '" title="' + smileys[iRow][i][2] + '" style="padding: 4px;" border="0" /></a> ');
		}
		this.oSmileyPopupWindow.document.write('<br />');
	}

	this.oSmileyPopupWindow.document.write('</td></tr>\n\t\t\t<tr><td align="center" class="windowbg"><a href="javascript:window.close();">' + sCloseText + '</a></td></tr>\n\t\t</table>');
	// Do the javascript required.
	this.oSmileyPopupWindow.document.write('<script language="JavaScript" type="text/javascript">\n');
	for (var iRow = 0; iRow < smileys.length; iRow++)
	{
		for (var i = 0; i < smileys[iRow].length; i++)
		{
			this.oSmileyPopupWindow.document.write('\n\twindow.opener.editorHandle' + postbox + '.addSmiley(\'' + smf_addslashes(smileys[iRow][i][0]) + '\', \'' + smf_addslashes(smileys[iRow][i][1]) + '\', \'' + smf_addslashes(smileys[iRow][i][2]) + '\');');
		}
	}
	this.oSmileyPopupWindow.document.write('\n</script>');
	this.oSmileyPopupWindow.document.write('\n\t</body>\n</html>');
	this.oSmileyPopupWindow.document.close();
}

// Set the focus for the editing window.
SmfEditor.prototype.setFocus = function()
{
	if (!this.bRichTextEnabled)
		this.oTextHandle.focus();
	else
		this.oFrameWindow.focus();
}

// Start up the spellchecker!
SmfEditor.prototype.spellCheckStart = function()
{
	if (!spellCheck)
		return false;

	// If we're in HTML mode we need to get the non-HTML text.
	if (this.bRichTextEnabled)
	{
		var sText = escape(this.getText(true, 1));

		this.tmpMethod = sendXMLDocument;
		this.tmpMethod(smf_prepareScriptUrl(smf_scripturl) + 'action=jseditor;view=0;sesc=' + this.sCurSessionId + ';xml', 'message=' + sText, this.onSpellCheckDataReceived);
		delete tmpMethod;
	}
	// Otherwise start spellchecking right away.
	else
		spellCheck(this.sFormId, this.sUniqueId);

	return true;
}

// This contains the spellcheckable text.
SmfEditor.prototype.onSpellCheckDataReceived = function(oXMLDoc)
{
	var sText = '';
	for (var i = 0; i < oXMLDoc.getElementsByTagName('message')[0].childNodes.length; i++)
		sText += oXMLDoc.getElementsByTagName('message')[0].childNodes[i].nodeValue;

	sText = sText.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');

	this.oTextHandle.value = sText;
	spellCheck(this.sFormId, this.sUniqueId);
}

// Function called when the Spellchecker is finished and ready to pass back.
SmfEditor.prototype.spellCheckEnd = function()
{
	// If HTML edit put the text back!
	if (this.bRichTextEnabled)
	{
		var sText = escape(this.getText(true, 0));

		this.tmpMethod = sendXMLDocument;
		this.tmpMethod(smf_prepareScriptUrl(smf_scripturl) + 'action=jseditor;view=1;sesc=' + this.sCurSessionId + ';xml', 'message=' + sText, smf_editorArray[this.iArrayPosition].onSpellCheckCompleteDataReceived);
		delete tmpMethod;
	}
	else
		this.setFocus();
}

// The corrected text.
SmfEditor.prototype.onSpellCheckCompleteDataReceived = function(oXMLDoc)
{
	var sText = '';
	for (var i = 0; i < oXMLDoc.getElementsByTagName('message')[0].childNodes.length; i++)
		sText += oXMLDoc.getElementsByTagName('message')[0].childNodes[i].nodeValue;

	this.insertText(sText, true);
	this.setFocus();
}

SmfEditor.prototype.resizeTextArea = function(newHeight, newWidth, is_change)
{
	// Work out what the new height is.
	if (is_change)
	{
		// We'll assume pixels but may not be.
		newHeight = this._calculateNewDimension(this.oTextHandle.style.height, newHeight);
		if (newWidth)
			newWidth = this._calculateNewDimension(this.oTextHandle.style.width, newWidth);
	}

	// Do the HTML editor - but only if it's enabled!
	if (this.bRichTextPossible)
	{
		this.oFrameHandle.style.height = newHeight;
		if (newWidth)
			this.oFrameHandle.style.width = newWidth;
	}
	// Do the text box regardless!
	this.oTextHandle.style.height = newHeight;
	if (newWidth)
		this.oTextHandle.style.width = newWidth;
}

// A utility instruction to save repetition when trying to work out what to change on a height/width.
SmfEditor.prototype._calculateNewDimension = function(old_size, change_size)
{
	// We'll assume pixels but may not be.
	changeReg = change_size.toString().match(/(-)?(\d+)(\D*)/);
	curReg = old_size.toString().match(/(\d+)(\D*)/);

	if (!changeReg[3])
		changeReg[3] = 'px';

	if (changeReg[1] == '-')
		changeReg[2] = 0 - changeReg[2];

	// Both the same type?
	if (changeReg[3] == curReg[2])
	{
		new_size = parseInt(changeReg[2]) + parseInt(curReg[1]);
		if (new_size < 50)
			new_size = 50;
		new_size = new_size.toString() + changeReg[3];
	}
	// Is the change a percentage?
	else if (changeReg[3] == '%')
		new_size = (parseInt(curReg[1]) + parseInt((parseInt(changeReg[2]) * parseInt(curReg[1])) / 100)).toString() + 'px';
	// Otherwise just guess!
	else
		new_size = (parseInt(curReg[1]) + (parseInt(changeReg[2]) / 10)).toString() + '%';

	return new_size;
}

// Register default keyboard shortcuts.
SmfEditor.prototype.registerDefaultShortcuts = function()
{
	if (is_ff)
	{
		this.registerShortcut('b', 'ctrl', 'b');
		this.registerShortcut('u', 'ctrl', 'u');
		this.registerShortcut('i', 'ctrl', 'i');
		this.registerShortcut('p', 'alt', 'preview');
		this.registerShortcut('s', 'alt', 'submit');
	}
}

// Register a keyboard shortcut.
SmfEditor.prototype.registerShortcut = function(sLetter, sModifiers, sCodeName)
{
	if (!sCodeName)
		return;

	var oNewShortcut = {
		code : sCodeName,
		key: sLetter.toUpperCase().charCodeAt(0),
		alt : false,
		ctrl : false
	};

	var aSplitModifiers = sModifiers.split(',');
	for(var i = 0, n = aSplitModifiers.length; i < n; i++)
		if (typeof(oNewShortcut[aSplitModifiers[i]]) != 'undefined')
			oNewShortcut[aSplitModifiers[i]] = true;

	this.aKeyboardShortcuts[this.aKeyboardShortcuts.length] = oNewShortcut;
}

// Check whether the key has triggered a shortcut?
SmfEditor.prototype.checkShortcut = function(oEvent)
{
	// To be a shortcut it needs to be one of these, duh!
	if (!oEvent.altKey && !oEvent.ctrlKey)
		return false;

	sReturnCode = false;

	// Let's take a look at each of our shortcuts shall we?
	for (i = 0; i < this.aKeyboardShortcuts.length; i++)
	{
		// Found something?
		if (oEvent.altKey == this.aKeyboardShortcuts[i].alt && oEvent.ctrlKey == this.aKeyboardShortcuts[i].ctrl && oEvent.keyCode == this.aKeyboardShortcuts[i].key)
			sReturnCode = this.aKeyboardShortcuts[i].code;
	}

	return sReturnCode;
}

// The actual event check for the above!
SmfEditor.prototype.shortcutCheck = function(oEvent)
{
	sFoundCode = this.checkShortcut(oEvent);

	// Run it and exit.
	if (sFoundCode)
	{
		cancelEvent = false;
		if (sFoundCode == 'submit')
		{
			// So much to do!
			submitThisOnce(document.getElementById(this.sFormId));
			submitonce(document.getElementById(this.sFormId));
			saveEntities();
			document.getElementById(this.sFormId).submit();

			cancelEvent = true;
		}
		if (sFoundCode == 'preview')
		{
			previewPost();
			cancelEvent = true;
		}
		if (document.getElementById('cmd_' + sFoundCode))
		{
			oEmulateObject = document.getElementById('cmd_' + sFoundCode);
			this.buttonEventHandler(oEmulateObject, 'click');
			cancelEvent = true;
		}

		if (cancelEvent)
		{
			if (is_ie && oEvent.cancelBubble)
				oEvent.cancelBubble = true;
			else if (oEvent.stopPropagation)
			{
				oEvent.stopPropagation();
				oEvent.preventDefault();
			}

			return false;
		}
	}

	return true;
}

// This is the method called after clicking the resize bar.
SmfEditor.prototype.startResize = function(oEvent)
{
	if (window.event)
		oEvent = window.event;

	if (!oEvent || window.smf_oCurrentResizeEditor != null)
		return true;

	window.smf_oCurrentResizeEditor = this.iArrayPosition;

	var aCurCoordinates = smf_mousePose(oEvent);
	this.oSmfEditorCurrentResize.old_y = aCurCoordinates[1];
	this.oSmfEditorCurrentResize.old_rel_y = null;
	this.oSmfEditorCurrentResize.cur_height = parseInt(this.oTextHandle.style.height);

	// Set the necessary events for resizing.
	var oResizeEntity = is_ie ? document : window;
	oResizeEntity.addEventListener('mousemove', this.aEventWrappers.resizeOverDocument, false);
	this.oFrameDocument.addEventListener('mousemove', this.aEventWrappers.resizeOverIframe, false);
	document.addEventListener('mouseup', this.aEventWrappers.endResize, true);
	this.oFrameDocument.addEventListener('mouseup', this.aEventWrappers.endResize, true);

	return false;
}

// This is kind of a cheat, as it only works over the IFRAME.
SmfEditor.prototype.resizeOverIframe = function(oEvent)
{
	if (window.event)
		oEvent = window.event;

	if (!oEvent || window.smf_oCurrentResizeEditor == null)
		return true;

	newCords = smf_mousePose(oEvent);

	if (this.oSmfEditorCurrentResize.old_rel_y == null)
		this.oSmfEditorCurrentResize.old_rel_y = newCords[1];
	else
	{
		var iNewHeight = newCords[1] - this.oSmfEditorCurrentResize.old_rel_y + this.oSmfEditorCurrentResize.cur_height;
		if (iNewHeight < 0)
			this.endResize();
		else
			this.resizeTextArea(iNewHeight + 'px', 0, false);
	}

	return false;
}

// This resizes an editor.
SmfEditor.prototype.resizeOverDocument = function (oEvent)
{
	if (window.event)
		oEvent = window.event;

	if (!oEvent || window.smf_oCurrentResizeEditor == null)
		return true;

	newCords = smf_mousePose(oEvent);

	var iNewHeight = newCords[1] - this.oSmfEditorCurrentResize.old_y + this.oSmfEditorCurrentResize.cur_height;
	if (iNewHeight < 0)
		this.endResize();
	else
		this.resizeTextArea(iNewHeight + 'px', 0, false);

	return false;
}

SmfEditor.prototype.endResize = function (oEvent)
{
	if (window.event)
		oEvent = window.event;

	if (window.smf_oCurrentResizeEditor == null)
		return true;

	window.smf_oCurrentResizeEditor = null;

	// Remove the event...
	var oResizeEntity = is_ie ? document : window;
	oResizeEntity.removeEventListener('mousemove', this.aEventWrappers.resizeOverDocument, false);
	this.oFrameDocument.removeEventListener('mousemove', this.aEventWrappers.resizeOverIframe, false);
	document.removeEventListener('mouseup', this.aEventWrappers.endResize, true);
	this.oFrameDocument.removeEventListener('mouseup', this.aEventWrappers.endResize, true);

	return false;
}
