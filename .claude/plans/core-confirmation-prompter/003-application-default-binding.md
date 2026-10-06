# Task 003: Default binding in Application

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Bind `ConfirmationPrompterInterface` to `StdinConfirmationPrompter` in `Application::initialize()` before module bindings are registered, so modules can override it.

## Context
- Related files: packages/core/src/Application.php, packages/core/tests (Application tests)
- Gotcha: `Input` can't be autowired because its `array $arguments` parameter has no default. Outside a `CommandRunner::run()`, resolving the interface throws `BindingException`. Tests must `instance(Input::class, ...)` and `instance(Output::class, ...)` before `get(ConfirmationPrompterInterface::class)`.
- Use `$this->container->bind(...)` right after the container is created, before the `foreach ($this->modules ...) registerModule` loop.
- Override path: a module binding, or a Preference that replaces `ConfirmationPrompterInterface`. A Preference on `StdinConfirmationPrompter` is NOT applied, because `Container::resolve()` checks preferences only for the requested id, not for the binding target.

## Requirements (Test Descriptions)
- [ ] `it binds ConfirmationPrompterInterface to StdinConfirmationPrompter by default`
- [ ] `it lets a module binding replace the default confirmation prompter`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
