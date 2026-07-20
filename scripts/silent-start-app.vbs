'=============================================================================
' Silent launcher - Exterior Students System
' Starts Herd + XAMPP MySQL/Apache, then opens Google Chrome in app mode
' Uses the SAME Chrome profile as the normal browser so print/window
' settings match 100% (no separate ExteriorStudent\ChromeApp profile)
'=============================================================================
Option Explicit

Dim sh, fso, xampp, herdBat, appUrl, chromeExe, chromeUserData, profileDir, cmd

Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

xampp = "C:\xampp"
herdBat = sh.ExpandEnvironmentStrings("%USERPROFILE%") & "\.config\herd\bin\herd.bat"
appUrl = "http://exterior_student.test"
chromeUserData = sh.ExpandEnvironmentStrings("%LOCALAPPDATA%") & "\Google\Chrome\User Data"
profileDir = "Default"
chromeExe = ""

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

If Not ProcessExists("mysqld.exe") Then
  If fso.FileExists(xampp & "\mysql\bin\mysqld.exe") Then
    RunHidden "cmd /c cd /d """ & xampp & """ && start """" /b """ & xampp & "\mysql\bin\mysqld.exe"" --defaults-file=""" & xampp & "\mysql\bin\my.ini"" --standalone"
  End If
End If

If Not ProcessExists("httpd.exe") Then
  If fso.FileExists(xampp & "\apache\bin\httpd.exe") Then
    RunHidden "cmd /c cd /d """ & xampp & """ && start """" /b """ & xampp & "\apache\bin\httpd.exe"""
  End If
End If

If fso.FileExists(herdBat) Then
  RunHidden "cmd /c call """ & herdBat & """ start -q -n"
End If

WScript.Sleep 5000

chromeExe = ResolveChromeExe()

If chromeExe <> "" Then
  ' Same User Data + Default profile as normal Chrome => identical print settings/UI direction
  cmd = """" & chromeExe & """"
  If fso.FolderExists(chromeUserData) Then
    cmd = cmd & " --user-data-dir=""" & chromeUserData & """ --profile-directory=" & profileDir
  End If
  cmd = cmd & " --app=" & appUrl & " --start-maximized --no-first-run --disable-session-crashed-bubble --disable-features=TranslateUI"
  sh.Run cmd, 1, False
Else
  sh.Popup "لم يتم العثور على Google Chrome. ثبّت Chrome ثم أعد المحاولة.", 8, "نظام الطلبة", 48
End If

Set sh = Nothing
Set fso = Nothing
