# Uploading a Project Folder to GitHub

1. Install Git:
   - Download and install Git from https://git-scm.com/downloads.

2. Set Up Git:
   - Configure Git with your name and email:
     git config --global user.name "Your Name"
     git config --global user.email "your_email@example.com"

3. Create a New Repository on GitHub:
   - Log in to GitHub.
   - Click on the "+" icon in the top-right corner and select "New repository."
   - Follow the prompts to create a new repository.

4. Initialize Git in Your Project Folder:
   - Navigate to your project folder in the terminal.
   - Initialize a new Git repository:
     cd /path/to/your/project
     git init

5. Add Your Files to the Repository:
   - Add all files in your project folder to the staging area:
     git add .

6. Commit Your Changes:
   - Commit your changes to the Git repository:
     git commit -m "Initial commit"

7. Link Your Local Repository to GitHub:
   - Copy the URL of the GitHub repository.
   - Link your local Git repository to the GitHub repository:
     git remote add origin <repository_url>

8. Push Your Changes to GitHub:
   - Push your committed changes to GitHub:
     git push -u origin master


# Updating Changes in Your GitHub Repository

1. Make Changes to Your Local Files:
   - Navigate to your project directory on your local machine.

2. Stage Your Changes:
   - Stage your changes for the next commit:
     git add .

3. Commit Your Changes:
   - Commit your changes to your local repository with a descriptive message:
     git commit -m "Descriptive commit message here"

4. Push Changes to GitHub:
   - Push your changes to your GitHub repository:
     git push origin master

5. Enter Your GitHub Credentials:
   - Enter your GitHub username and password or authenticate using a token.

6. Verify Changes on GitHub:
   - Visit your GitHub repository in a web browser to verify that your changes have been updated.


# Additional Helpful Git Commands

1. Check the Status of Your Repository:
   - See which files are staged, unstaged, or untracked:
     git status

2. View the Commit History:
   - See a list of previous commits, their messages, and other details:
     git log

3. Create a New Branch:
   - Create a new branch for new features, bug fixes, etc.:
     git branch <branch_name>

4. Switch Branches:
   - Switch to a different branch in your repository:
     git checkout <branch_name>

5. Merge Branches:
   - Merge changes from one branch into another:
     git merge <branch_name>

6. Discard Changes in Working Directory:
   - Discard changes in your working directory for a specific file:
     git checkout -- <file_name>

7. Discard Changes in Staging Area:
   - Unstage changes for a specific file:
     git reset HEAD <file_name>

8. Update Your Local Repository:
   - Fetch the latest changes from the remote repository:
     git fetch
   - Update your local repository with the latest changes:
     git pull

9. Clone a Repository:
   - Clone an existing repository from a remote location:
     git clone <repository_url>

10. Configure Remote Repositories:
    - View configured remote repositories:
      git remote -v
    - Add a new remote repository:
      git remote add <remote_name> <repository_url>
    - Remove a remote repository:
      git remote remove <remote_name>

11. Create a .gitignore File:
    - Ignore specific files or directories in your Git repository:
      Create a .gitignore file and add file/directory names to ignore.

12. Amend the Last Commit:
    - Add changes to the last commit or edit the commit message:
      git commit --amend

13. Undo a Commit:
    - Undo the last commit and keep changes in the working directory:
      git reset HEAD~1
    - Undo the last commit and discard changes:
      git reset --hard HEAD~1

14. Tagging:
    - Create a tag for a specific commit:
      git tag <tag_name>
    - Push tags to the remote repository:
      git push --tags

15. Show Changes Between Commits:
    - Show the changes made between two commits:
      git diff <commit_hash1> <commit_hash2>

16. Show Changes Made to a File:
    - Show the changes made to a specific file:
      git diff <file_name>

17. Stash Changes:
    - Temporarily store changes that are not ready to be committed:
      git stash

18. Apply Stashed Changes:
    - Apply the most recent stashed changes:
      git stash apply

19. Rebase:
    - Apply commits from one branch to another with more flexibility than merging:
      git rebase <branch_name>

20. Git Help:
    - Get help for a specific Git command:
      git help <command_name>
