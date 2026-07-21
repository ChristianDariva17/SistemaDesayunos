# Agent Execution Policy

## Allowed without additional authorization

- Read project files.
- Create files requested by the user.
- Edit existing files requested by the user.

## Requires explicit user authorization

The agent must not execute terminal commands unless the user explicitly authorizes it. This includes, but is not limited to:

- Git commands.
- Test, lint, build, or formatting commands.
- Composer, Artisan, npm, or other package and framework commands.
- Database, deployment, container, and operating-system commands.

Without explicit authorization, the agent must provide the exact command for the user to run instead of executing it. Authorization is exceptional and applies only to the command or clearly defined command scope approved by the user.
