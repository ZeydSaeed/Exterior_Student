'=============================================================================
' Silent launcher - Exterior Students System (SERVER PC)
' Starts XAMPP MySQL (NOT Apache) + Herd nginx, then opens Chrome in app mode.
'
' Ensures LAN IP 192.168.10.1 on the Ethernet adapter (for client laptops),
' keeps LAN nginx config enabled (listen 80), starts nginx, opens the app.
'=============================================================================
Option Explicit

Const SERVER_IP = "192.168.10.1"
Const APP_HOST = "exterior_student.test"

Dim sh, fso, xampp, herdHome, herdBat, nginxExe, nginxPrefix, nginxConf
Dim lanConf, lanDisabled, ensureIpPs1, scriptsDir, appUrl, chromeExe
Dim lanIpPresent, siteOk, i, errMsg, cmd, appProfile

Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

xampp = "C:\xampp"
herdHome = sh.ExpandEnvironmentStrings("%USERPROFILE%") & "\.config\herd"
herdBat = herdHome & "\bin\herd.bat"
nginxExe = "C:\Program Files\Herd\resources\app.asar.unpacked\resources\bin\nginx\nginx.exe"
nginxPrefix = herdHome & "\config\nginx"
nginxConf = nginxPrefix & "\nginx.conf"
lanConf = herdHome & "\config\pro\nginx\exterior-student-lan.conf"
lanDisabled = lanConf & ".disabled"
scriptsDir = fso.GetParentFolderName(WScript.ScriptFullName)
ensureIpPs1 = scriptsDir & "\ensure-server-lan-ip.ps1"
appUrl = "http://" & APP_HOST
chromeExe = ""
errMsg = ""

Function ResolveChromeExe()
  Dim candidates, i
  candidates = Array( _
    "C:\Program Files\Google\Chrome\Application\chrome.exe", _
    "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe", _
    sh.ExpandEnvironmentStrings("%LOCALAPPDATA%") & "\Google\Chrome\Application\chrome.exe" _
  )
  ResolveChromeExe = ""
  For i = 0 To UBound(candidates)
    If fso.FileExists(candidates(i)) Then
      ResolveChromeExe = candidates(i)
      Exit Function
    End If
  Next
End Function

Sub RunHidden(cmdLine)
  sh.Run cmdLine, 0, False
End Sub

Sub RunHiddenWait(cmdLine)
  sh.Run cmdLine, 0, True
End Sub

Function ProcessExists(processName)
  Dim svc, procs, p
  ProcessExists = False
  On Error Resume Next
  Set svc = GetObject("winmgmts:\\.\root\cimv2")
  Set procs = svc.ExecQuery("SELECT Name FROM Win32_Process WHERE Name='" & processName & "'")
  For Each p In procs
    ProcessExists = True
    Exit For
  Next
  On Error GoTo 0
End Function

Function IpAddressExists(ipAddress)
  Dim svc, adapters, a, addrs, addr
  IpAddressExists = False
  On Error Resume Next
  Set svc = GetObject("winmgmts:\\.\root\cimv2")
  Set adapters = svc.ExecQuery("SELECT IPAddress FROM Win32_NetworkAdapterConfiguration WHERE IPEnabled=True")
  For Each a In adapters
    If Not IsNull(a.IPAddress) Then
      addrs = a.IPAddress
      If IsArray(addrs) Then
        For Each addr In addrs
          If CStr(addr) = ipAddress Then
            IpAddressExists = True
            On Error GoTo 0
            Exit Function
          End If
        Next
      End If
    End If
  Next
  On Error GoTo 0
End Function

Sub EnsureLanConfigEnabled()
  If fso.FileExists(lanDisabled) And Not fso.FileExists(lanConf) Then
    fso.MoveFile lanDisabled, lanConf
  End If
End Sub

Sub EnsureServerLanIp()
  If Not fso.FileExists(ensureIpPs1) Then Exit Sub
  If IpAddressExists(SERVER_IP) Then Exit Sub
  ' Try elevated PowerShell to assign static IP on Ethernet
  On Error Resume Next
  sh.Run "powershell -NoProfile -ExecutionPolicy Bypass -Command ""Start-Process powershell -Verb RunAs -Wait -ArgumentList '-NoProfile -ExecutionPolicy Bypass -File \""" & ensureIpPs1 & "\"" -ServerIp " & SERVER_IP & "'""", 0, True
  On Error GoTo 0
  WScript.Sleep 1500
End Sub

Sub EnsureFirewallHttp()
  On Error Resume Next
  RunHiddenWait "cmd /c netsh advfirewall firewall show rule name=""Exterior Student HTTP"" >nul || netsh advfirewall firewall add rule name=""Exterior Student HTTP"" dir=in action=allow protocol=TCP localport=80"
  On Error GoTo 0
End Sub

Sub StopNginx()
  If ProcessExists("nginx.exe") Then
    RunHiddenWait "cmd /c taskkill /IM nginx.exe /F"
    WScript.Sleep 800
  End If
End Sub

Sub StartNginxExplicit()
  If Not fso.FileExists(nginxExe) Then Exit Sub
  If Not fso.FileExists(nginxConf) Then Exit Sub
  StopNginx
  RunHidden "cmd /c """"" & nginxExe & """ -p """ & nginxPrefix & "/"" -c """ & nginxConf & """"""
  WScript.Sleep 1500
End Sub

Function SiteReachable(url)
  Dim http
  SiteReachable = False
  On Error Resume Next
  Set http = CreateObject("MSXML2.ServerXMLHTTP.6.0")
  If http Is Nothing Then Set http = CreateObject("MSXML2.ServerXMLHTTP")
  If http Is Nothing Then
    On Error GoTo 0
    Exit Function
  End If
  http.open "GET", url, False
  http.setTimeouts 2000, 2000, 4000, 6000
  http.send
  If Err.Number = 0 Then
    If http.Status >= 200 And http.Status < 500 Then
      SiteReachable = True
    End If
  End If
  Err.Clear
  On Error GoTo 0
End Function

'---------------- main ----------------
EnsureServerLanIp
lanIpPresent = IpAddressExists(SERVER_IP)
EnsureLanConfigEnabled

' MySQL (XAMPP) — do not start Apache
If Not ProcessExists("mysqld.exe") Then
  If fso.FileExists(xampp & "\mysql\bin\mysqld.exe") Then
    RunHidden "cmd /c cd /d """ & xampp & """ && start """" /b """ & xampp & "\mysql\bin\mysqld.exe"" --defaults-file=""" & xampp & "\mysql\bin\my.ini"" --standalone"
    WScript.Sleep 2000
  End If
End If

If ProcessExists("httpd.exe") Then
  RunHiddenWait "cmd /c taskkill /IM httpd.exe /F"
  WScript.Sleep 1000
End If

If fso.FileExists(herdBat) Then
  RunHidden "cmd /c call """ & herdBat & """ start -q -n"
  WScript.Sleep 2500
End If

EnsureFirewallHttp
StartNginxExplicit

siteOk = False
For i = 1 To 12
  If SiteReachable(appUrl) Then
    siteOk = True
    Exit For
  End If
  If i = 4 Then StartNginxExplicit
  WScript.Sleep 1000
Next

If Not siteOk Then
  errMsg = "تعذر تشغيل الموقع على السيرفر." & vbCrLf & vbCrLf & _
    "تأكد من:" & vbCrLf & _
    "1) كابل السويج موصول بمحول Ethernet على السيرفر" & vbCrLf & _
    "2) تشغيل هذا الملف كمسؤول مرة لتعيين IP " & SERVER_IP & vbCrLf & _
    "3) أو شغّل: scripts\configure-server-herd-lan.bat (كمسؤول)" & vbCrLf & vbCrLf & _
    "جرّب محلياً: " & appUrl
  sh.Popup errMsg, 20, "نظام إدارة الطلبة", 48
  WScript.Quit 1
End If

If Not lanIpPresent And Not IpAddressExists(SERVER_IP) Then
  sh.Popup "الموقع يعمل محلياً، لكن عنوان السيرفر " & SERVER_IP & " غير مفعّل." & vbCrLf & vbCrLf & _
    "لابتوبات العملاء لن تتصل حتى:" & vbCrLf & _
    "1) تشغّل silent-start-app.vbs أو configure-server-herd-lan.bat كمسؤول" & vbCrLf & _
    "2) يكون كابل السويج موصولاً بـ Ethernet على السيرفر", 12, "نظام إدارة الطلبة", 64
End If

chromeExe = ResolveChromeExe()
If chromeExe = "" Then
  sh.Popup "لم يتم العثور على Google Chrome. ثبّت Chrome ثم أعد المحاولة.", 8, "نظام إدارة الطلبة", 48
  WScript.Quit 1
End If

appProfile = sh.ExpandEnvironmentStrings("%LOCALAPPDATA%") & "\ExteriorStudent\ChromeApp"
If Not fso.FolderExists(appProfile) Then
  On Error Resume Next
  fso.CreateFolder sh.ExpandEnvironmentStrings("%LOCALAPPDATA%") & "\ExteriorStudent"
  fso.CreateFolder appProfile
  On Error GoTo 0
End If

cmd = """" & chromeExe & """ --user-data-dir=""" & appProfile & """ --profile-directory=Default"
cmd = cmd & " --app=" & appUrl & " --start-maximized --no-first-run --disable-session-crashed-bubble --disable-features=TranslateUI"
sh.Run cmd, 1, False

Set sh = Nothing
Set fso = Nothing
