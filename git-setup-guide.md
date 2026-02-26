# Git Setup Guide for PHP Projects

## Initial Setup

```bash
# 1. Install Git (if not already installed)
sudo apt install git          # Ubuntu/Debian
brew install git              # macOS

# 2. Configure your identity (global)
git config --global user.name  "Your Name"
git config --global user.email "you@example.com"
git config --global init.defaultBranch main

# 3. Initialize repository in your project folder
cd /path/to/php-practice
git init

# 4. Add .gitignore FIRST (before any commits)
# (Already created as .gitignore in this project)

# 5. Stage all files
git add .

# 6. First commit
git commit -m "feat: initial PHP practice curriculum"
```

---

## Working with Branches

```bash
# Create and switch to a new branch
git checkout -b feature/rest-api

# List all branches
git branch -a

# Merge a feature branch into main
git checkout main
git merge feature/rest-api

# Delete merged branch
git branch -d feature/rest-api
```

---

## Conventional Commits

Follow this commit message standard:
```
type(scope): short description

Types: feat | fix | docs | style | refactor | test | chore
```

Examples:
```bash
git commit -m "feat(auth): add JWT token generation"
git commit -m "fix(validation): handle null email input"
git commit -m "docs(readme): add level 5 MVC explanation"
git commit -m "refactor(router): extract dispatch logic"
```

---

## Connecting to GitHub

```bash
# Create a repo on GitHub first, then:
git remote add origin https://github.com/username/php-practice.git
git branch -M main
git push -u origin main

# Future pushes
git push

# Pull latest changes
git pull origin main
```

---

## Useful Aliases

Add to `~/.gitconfig`:
```ini
[alias]
    st   = status
    co   = checkout
    br   = branch
    lg   = log --oneline --graph --all --decorate
    undo = reset HEAD~1
    save = stash push -m
```

---

## Tagging Releases

```bash
git tag -a v1.0.0 -m "Level 1 complete"
git push origin --tags
```
