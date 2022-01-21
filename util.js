function cngStatus()
{
	alert ("Status will be changed \n Status - Assigned")
}
function viewMsg()
{
	self.location.replace("message.html")
}
function sendMsg()
{
	alert ("Send Message")
	self.location.replace("index.html")
}
function notImpl()
{
	alert ("Not Implemented Yet");
}
function cngSetting()
{
	alert ("Change settings")
	self.location.replace("index.html")
}
function verify(msg, v)
{
	if (confirm(msg))
	{
		self.location.href = v;
		return true;
	}
	else
	{
		return false; 
	}
}
function checkdate ()
{
	var fmonth = parseInt(document.formDist.fdm.value)-1;
	var fd = new Date(document.formDist.fdy.value, fmonth, document.formDist.fdd.value);
	var tmonth = parseInt(document.formDist.tdm.value)-1;
	var td = new Date(document.formDist.tdy.value, tmonth, document.formDist.tdd.value);

	if (document.formDist.fdy.value == 0 && fmonth == -1 && document.formDist.fdd.value == 0 )
	{
		document.formDist.submit();
		return true;
	}
	validdate(fmonth, document.formDist.fdy.value);
	if (document.formDist.fdy.value < 1900 || fmonth < 0 || document.formDist.fdd.value < 1 )
	{
		window.alert("From date is wrong!");
		return false;
	}
	if (document.formDist.fdy.value > 2100 || fmonth > 11 || document.formDist.fdd.value > maxdays)
	{
		window.alert("From date is wrong!");
		return false;			
	}
	validdate(tmonth, document.formDist.tdy.value);
	if (document.formDist.tdy.value < 1900 || tmonth < 0 || document.formDist.tdd.value < 1 )
	{
		window.alert("To date is wrong!");
		return false;			
	}
	if (document.formDist.tdy.value > 2100 || tmonth > 11 || document.formDist.tdd.value > maxdays)
	{
		window.alert("To date is wrong!");
		return false;			
	} 
	if (fd > td)
	{
		window.alert("From date is more than To date!");
		return false;			
	}
	document.formDist.submit();
	return true;
}
var maxdays = 0;
function validdate(m, y)
{
	switch(m)
	{
		case 0:
			maxdays = 31;
			break; 
		case 1:
			maxdays = 28;
			if ((y % 4) == 0)
			{
				maxdays = 29;
				if ((y % 100) == 0 && (y % 400) != 0) {maxdays = 28; }
			}
			break;
		case 2:
			maxdays = 31;
			break; 
		case 3:
			maxdays = 30;
			break; 
		case 4:
			maxdays = 31; 
			break; 
		case 5:
			maxdays = 30; 
			break; 
		case 6:
			maxdays = 31; 
			break; 
		case 7:
			maxdays = 31; 
			break; 
		case 8:
			maxdays = 30; 
			break; 
		case 9:
			maxdays = 31; 
			break; 
		case 10:
			maxdays = 30; 
			break; 
		case 11:
			maxdays = 31; 			 
			break; 
	}
}
function cl_checkb(names)
{
	var ss, i;
	ss = names.split("/");
	for (i=0; i < ss.length-1; i++)
	{
		eval ("document.formDist."+ss[i]+".checked = false;");
	}
}

