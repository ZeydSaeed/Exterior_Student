'=============================================================================
' Client launcher - Exterior Students System (client laptops on LAN)
' Opens the app from the main PC (server). Does NOT start Herd/XAMPP/MySQL.
'
' Before first use on each laptop:
'   1) Install QTS1081B USB-LAN driver
'   2) Connect to switch, set static IP (see README-CLIENT-NETWORK.txt)
'   3) Run install-client-hosts.bat as Administrator (once)
'   4) Change SERVER_IP below if needed
'=============================================================================
Option Explicit

Const SERVER_IP = "192.168.10.1"
Const APP_HOST = "exterior_student.test"
Const USE_HOSTS_NAME = True

Dim sh, fso, appUrl, chromeExe, chromeUserData, profileDir, cmd, hostsPath, hostsOk

Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

If USE_HOSTS_NAME Then
  appUrl = "http://" & APP_HOST
  hostsPath = sh.ExpandEnvironmentStrings("%SystemRoot%") & "\System32\drivers\etc\hosts"
  hostsOk = HostsPointsToServer(hostsPath, APP_HOST, SERVER_IP)
  If Not hostsOk Then
    sh.Popup "hosts file is not configured." & vbCrLf & vbCrLf & _
      "Run as Admin: install-client-hosts.bat" & vbCrLf & _
      "Or add this line to hosts:" & vbCrLf & _
      SERVER_IP & "    " & APP_HOST, 12, "Exterior Student - Client", 48
    WScript.Quit 1
  End If
Else
  appUrl = "http://" & SERVER_IP
End If

chromeExe = ResolveChromeExe()
If chromeExe = "" Then
  sh.Popup "Google Chrome not found. Install Chrome and try again.", 8, "Exterior Student", 48
  WScript.Quit 1
End If

If Not ServerReachable(appUrl) Then
  If ServerReturnsXamppPage(appUrl) Then
    sh.Popup "Server is showing XAMPP page instead of Exterior Student." & vbCrLf & vbCrLf & _
      "Fix on SERVER PC (once):" & vbCrLf & _
      "1) Run scripts\configure-server-herd-lan.bat" & vbCrLf & _
      "2) Run silent-start-app.vbs (MySQL + Herd only, not Apache)" & vbCrLf & vbCrLf & _
      "Then run silent-start-client.vbs again on this laptop.", 16, "Exterior Student - Client", 48
  Else
    sh.Popup "Cannot reach server at " & appUrl & vbCrLf & vbCrLf & _
      "Check on CLIENT laptop:" & vbCrLf & _
      "1) USB adapter QTS1081B connected (Ethernet = Connected)" & vbCrLf & _
      "2) Static IP e.g. 192.168.10.11 / mask 255.255.255.0" & vbCrLf & _
      "3) cmd: ping " & SERVER_IP & vbCrLf & vbCrLf & _
      "Check on SERVER PC:" & vbCrLf & _
      "4) Run configure-server-herd-lan.bat once" & vbCrLf & _
      "5) Run silent-start-app.vbs (Herd + MySQL running)" & vbCrLf & _
      "6) Open http://exterior_student.test on server" & vbCrLf & _
      "7) Firewall: allow inbound TCP port 80" & vbCrLf & _
      "8) Server IP must be " & SERVER_IP, 16, "Exterior Student - Client", 48
  End If
  WScript.Quit 1
End If

chromeUserData = sh.ExpandEnvironmentStrings("%LOCALAPPDATA%") & "\Google\Chrome\User Data"
profileDir = "Default"

cmd = """" & chromeExe & """"
If fso.FolderExists(chromeUserData) Then
  cmd = cmd & " --user-data-dir=""" & chromeUserData & """ --profile-directory=" & profileDir
End If
cmd = cmd & " --app=" & appUrl & " --start-maximized --no-first-run --disable-session-crashed-bubble --disable-features=TranslateUI"
sh.Run cmd, 1, False

Set sh = Nothing
Set fso = Nothing

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

Function HostsPointsToServer(hostsFile, hostName, serverIp)
  Dim stream, line, parts, i, ip, token
  HostsPointsToServer = False
  If Not fso.FileExists(hostsFile) Then Exit Function
  Set stream = fso.OpenTextFile(hostsFile, 1, False)
  Do Until stream.AtEndOfStream
    line = Trim(stream.ReadLine)
    If line <> "" And Left(line, 1) <> "#" Then
      parts = Split(Replace(line, vbTab, " "), " ")
      ip = Trim(parts(0))
      For i = 1 To UBound(parts)
        token = Trim(parts(i))
        If token <> "" Then
          If LCase(token) = LCase(hostName) And ip = serverIp Then
            HostsPointsToServer = True
            stream.Close
            Exit Function
          End If
        End If
      Next
    End If
  Loop
  stream.Close
End Function

Function ServerReachable(url)
  Dim http, body, gotHttpResponse
  ServerReachable = False
  gotHttpResponse = False
  On Error Resume Next
  Set http = CreateObject("MSXML2.ServerXMLHTTP.6.0")
  If http Is Nothing Then
    Set http = CreateObject("MSXML2.ServerXMLHTTP")
  End If
  If http Is Nothing Then
    ServerReachable = ServerReachableViaPing()
    Exit Function
  End If
  http.open "GET", url, False
  http.setTimeouts 3000, 3000, 5000, 8000
  http.send
  If Err.Number = 0 Then
    If http.Status >= 200 And http.Status < 500 Then
      gotHttpResponse = True
      body = LCase(http.responseText)
      If InStr(body, "welcome to xampp") = 0 And InStr(body, "xampp apache") = 0 Then
        ServerReachable = True
      End If
    End If
  End If
  If Not gotHttpResponse And Not ServerReachable Then
    ServerReachable = ServerReachableViaPing()
  End If
  On Error GoTo 0
End Function

Function ServerReturnsXamppPage(url)
  Dim http, body
  ServerReturnsXamppPage = False
  On Error Resume Next
  Set http = CreateObject("MSXML2.ServerXMLHTTP.6.0")
  If http Is Nothing Then
    Set http = CreateObject("MSXML2.ServerXMLHTTP")
  End If
  If http Is Nothing Then Exit Function
  http.open "GET", url, False
  http.setTimeouts 3000, 3000, 5000, 8000
  http.send
  If Err.Number = 0 And http.Status >= 200 And http.Status < 500 Then
    body = LCase(http.responseText)
    If InStr(body, "welcome to xampp") > 0 Or InStr(body, "xampp apache") > 0 Then
      ServerReturnsXamppPage = True
    End If
  End If
  On Error GoTo 0
End Function

Function ServerReachableViaPing()
  Dim exitCode
  On Error Resume Next
  exitCode = sh.Run("cmd /c ping -n 1 -w 1500 " & SERVER_IP, 0, True)
  ServerReachableViaPing = (exitCode = 0)
  On Error GoTo 0
End Function
