// Add a fix for code stuff?
if ((is_ie && !is_ie4) || is_safari || is_ff)
	add_load_event(smf_codeBoxFix);

// The purpose of this code is to fix the height of overflow: auto div blocks, because IE can't figure it out for itself.
function smf_codeBoxFix()
{
	var codeFix = document.getElementsByTagName("code");
	for (var i = codeFix.length - 1; i >= 0; i--)
	{
		if (is_safari && codeFix[i].offsetHeight < 20)
			codeFix[i].style.height = (codeFix[i].offsetHeight + 20) + "px";

		else if (is_ff && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0))
			codeFix[i].style.overflow = "scroll";

		else if (typeof(codeFix[i].currentStyle) != 'undefined' && codeFix[i].currentStyle.overflow == "auto" && (codeFix[i].currentStyle.height == "" || codeFix[i].currentStyle.height == "auto") && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0) && (codeFix[i].offsetHeight != 0))
			codeFix[i].style.height = (codeFix[i].offsetHeight + 24) + "px";
	}

	// !!! Is this still needed?
	if (!is_ff)
	{
		var divFix = document.getElementsByTagName("div");
		for (var i = divFix.length - 1; i > 0; i--)
		{
			if (is_safari)
			{
				if ((divFix[i].className == "post" || divFix[i].className == "signature") && divFix[i].offsetHeight < 20)
					divFix[i].style.height = (divFix[i].offsetHeight + 20) + "px";
			}
			else
			{
				if (divFix[i].currentStyle.overflow == "auto" && (divFix[i].currentStyle.height == "" || divFix[i].currentStyle.height == "auto") && (divFix[i].scrollWidth > divFix[i].clientWidth || divFix[i].clientWidth == 0) && (divFix[i].offsetHeight != 0 || divFix[i].className == "code"))
					divFix[i].style.height = (divFix[i].offsetHeight + 24) + "px";
			}
		}
	}
}

function smf_addButton(sButtonStripId, bUseImage, oOptions)
{
	var oButtonStrip = document.getElementById(sButtonStripId);
	var aItems = oButtonStrip.getElementsByTagName('span');

	// Remove the 'last' class from the last item.
	var oLastSpan = aItems[aItems.length - 1];
	oLastSpan.className = oLastSpan.className.replace(/\s*last/, '');

	// Add the button.
	var oButtonStripList = oButtonStrip.getElementsByTagName('ul')[0];
	var oNewButton = document.createElement('li');
	setInnerHTML(oNewButton, '<a href="' + oOptions.sUrl + '" ' + (typeof(oOptions.sCustom) == 'string' ? oOptions.sCustom : '') + '><span class="last"' + (typeof(oOptions.sId) == 'string' ? ' id="' + oOptions.sId + '"': '') + '>' + oOptions.sText + '</span></a>');

	oButtonStripList.appendChild(oNewButton);
}