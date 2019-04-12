Dim oShell,oShell2
Set oShell2 = WScript.CreateObject ("WScript.Shell")
Set oShell = CreateObject("Shell.Application")
oShell2.run "cmd /K CD php/ & install.cmd & exit"
wscript.sleep (100)
oShell2.run "cmd /K CD nginx/ & install.cmd & exit"
wscript.sleep (100)
oShell.ShellExecute "mariadb\install.cmd", , , "runas", 1
wscript.quit