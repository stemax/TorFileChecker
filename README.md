# TorFileChecker
TorFileChecker - class for creating/checking the actual snapshot of project structure for developers.

>If your project is completed or in the final stage, you can easily take a snapshot of the project file structure and if a file will be changed (hacked) then you can always find where, when and what was changed. 
This class will always be able to help understand bottlenecks and quickly identify malicious files.

**Create project snapshot.**
---
Steps:

1. Upload torfilechecker.php into your project folder.
2. Open torfilechecker.php in browser
3. Choose project folder in selectlist on [New snapshot] section
4. Write snapshot name (optional) [ex: file_snapshot_NAME_2016-12-06.json]
5. Click on "Generate new snapshot" button
:clap:

Default folder for snapshots on server: home_folder/tor_snapshots
(Also, you can download snapshot file and save local version)

**Compare Original vs Current project structure**
---
Steps:

1. Upload torfilechecker.php into your project folder.
2. Open torfilechecker.php in browser
3. Choose a latest snapshot in selectlist on [Compare snapshot] section
4. Choose project folder in selectlist on [Compare snapshot] section
5. Click on "Compare" button

So, if somebody changed your project files or folders then you will see in final comparing report.
---

Happy codding.

Actual Ver: 1.0.
:+1:
