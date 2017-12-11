function CreateRequest()
{
    var Request = false;

    if (window.XMLHttpRequest)
    {
        //Gecko-����������� ��������, Safari, Konqueror
        Request = new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
        //Internet explorer
        try
        {
             Request = new ActiveXObject("Microsoft.XMLHTTP");
        }    
        catch (CatchException)
        {
             Request = new ActiveXObject("Msxml2.XMLHTTP");
        }
    }
 
    if (!Request)
    {
        alert("���������� ������� XMLHttpRequest");
    }
    
    return Request;
}

/*
������� ������� ������� � ����� �� �������
r_method  - ��� �������: GET ��� POST
r_path    - ���� � �����
r_args    - ��������� ���� a=1&b=2&c=3...
r_handler - �������-���������� ������ �� �������
*/
function SendRequest(r_method, r_path, r_args, ok_handler, er_handler, r_handler_strings)
{
    //������ ������
    var Request = CreateRequest();
    
    //��������� ������������� ������� ��� ���
    if (!Request)
    {
        return;
    }
    
    //��������� ���������������� ����������
    Request.onreadystatechange = function()
    {
        //���� ����� ������� ��������
        if (Request.readyState == 4)
        {
			if (Request.status == 200)
			{
				ok_handler(Request);
			}
			if (Request.status == 204)
			{
				ok_handler(Request);
			}
			else if(Request.status >= 400 & Request.status <= 451)
			{
				er_handler(Request);
			}
        }
    }
    
    //���������, ���� ��������� ������� GET-������
    if (r_method.toLowerCase() == "get" && r_args.length > 0) r_path += "?" + r_args;
    
    //�������������� ����������
    Request.open(r_method, r_path, true);
    
    if (r_method.toLowerCase() == "post")
    {
        //���� ��� POST-������
        //������������� ���������
        Request.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=utf-8");
        Request.send(r_args);
    }
    else
    {
        Request.send(null);
    }
}