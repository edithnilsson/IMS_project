1. Install Composer — Windows
    Download the installer
    Go to getcomposer.org/download and download Composer-Setup.exe (the Windows installer, near the top of the page). Run it.

2. Point it at MAMP's PHP
    The installer will ask you to locate php.exe. Don't let it search automatically — point it directly at MAMP's copy, something like:
    C:\MAMP\bin\php\php8.x.x\php.exe

    The exact folder name depends on which PHP version MAMP installed for you — open C:\MAMP\bin\php\ in File Explorer to see the folder name, then pick the php.exe inside it.

    Finish the rest of the installer with the default options, then close and reopen Command Prompt or PowerShell or VSCode so it picks up the new settings.

3. Verify it worked
    composer --version
    You should see something like Composer version 2.x.x

4. Install PHPMailer in the project
    Open projectfolder in VSCode:
    In the terminal you should be in the IMS-project
    "path/to/IMS-Project"

    Run:
    composer install

5. In mailer.php
    Change password in line 23:
    "$mail->Password = "16charpw";"
    Password is in the whatsapp group

----------------------------------------------------------------------------------

1. Install composer - macOS
    Check first: run brew --version in Terminal. If that prints a version run:
    brew install composer

2. Verify it worked
    composer --version

3. Install PHPMailer in the project
    Open projectfolder in VSCode:
    In the terminal you should be in the IMS-project
    "path/to/IMS-Project"

    Run:
    composer install

4. In mailer.php
    Change password in line 23:
    "$mail->Password = "16charpw";"
    Password is in the whatsapp group