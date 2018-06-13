<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>{{$data['title']}}</title>
</head>
<style>
	a{ text-decoration:none; color:#831025;}
	a:hover{ color:#be1d3b}
	
	p{ color:#666}
	.pic{
	border: 1px dashed #604c3f;
    background: #fff;
    padding: 5px;
	}
	
</style>

<body>
<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
  <tbody><tr>
    <td height="39">&nbsp;</td>
  </tr>
</tbody>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tbody>
  <tr>
    <td align="center">
    	<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
        <tbody>
        <tr>
        <td>
        
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="border-radius:5px 5px 0 0; background-color:#7ecef4;">
              	<tbody>
              	
                
                <tr>
                    <td width="75%" align="left" bgcolor="#fbf6b8" style="padding:0 20px" background-color="#FBF6B8">
                        <p>{{$data['text']}} {{$data['name']}} :</p>
                        <p>{{$data['message']}}</p>
                    </td>
                </tr>
                
                
            	</tbody>
            </table>
        </td>
        </tr>
      	</tbody>
      	</table>
  	</td>
  </tr>
</tbody>
</table>

</body>
</html>
