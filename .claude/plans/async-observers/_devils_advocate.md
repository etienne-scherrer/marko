# Devil's Advocate Review: async-observers

## Critical (Must fix before building)

1. **Contract signature undefined (001 -> 002).** Task 001 creates `AsyncObserverDispatcherInterface` but never states its method; task 002 implements it. Fix: pin `public function dispatch(ObserverDefinition $definition, Event $event): void` in 001 and 002, plus the exception factory name `EventException::noAsyncObserverDispatcher(string $observerClass, string $eventClass)`.

2. **Existing core tests contradict the new behaviour (001).** `packages/core/tests/Unit/Event/EventDispatcherTest.php` has `accepts optional queue` (asserts a 3rd `queue` param typed `QueueInterface`), `queues async observers`, `executes sync observers immediately` (both use `FakeQueue`), and `falls back when no queue` (asserts inline execution, which now must throw). It also imports `Marko\Queue\AsyncObserverJob`/`QueueInterface`. Fix: 001 must remove or rewrite these using an in-test fake `AsyncObserverDispatcherInterface`, and drop the `Marko\Queue` imports.

3. **SyncQueue construction sites not covered (002).** `SyncQueueFactory::create()` does `new SyncQueue()`, and `packages/queue-sync/tests/SyncQueueTest.php` (9x), `tests/Unit/SyncQueueFactoryTest.php`, `tests/ModuleTest.php`, `packages/queue/tests/Feature/IntegrationTest.php` construct it with no args. Giving `SyncQueue` a container and `JobEnvelope` needs all of these updated, and the factory has to inject both. If the envelope is skipped, `AsyncObserverJob::handle()` calls `unserialize()` on the signed envelope string. That returns `false`, and the observer gets `handle(false)`, which fails with a TypeError.

## Important (Should fix before building)

4. **Worker crashes on a job's final failure because it serializes the container (002).** `Worker::handleFailedJob()` calls `$this->jobEnvelope->wrap($job->serialize())`, which is `serialize($this)`. That runs after `setContainer()`, so it tries to serialize the whole Container (Closure bindings, PDO instances) and throws outside the try block. Once async observers really go to a queue, a permanently failing observer takes down `queue:work` and never reaches `failed_jobs`. Worker.php is off-limits (#162). Fix inside `AsyncObserverJob`: in a `finally` block at the end of `handle()`, null out `$container`/`$jobEnvelope` so the failed-job payload serializes cleanly.

5. **The integration known-gap for #163 is not flipped (003).** `tests/Integration/App/KnownGapsTest.php` has a `->todo(issue: 163)` that, by that file's contract, this ticket turns into a real test. Move it to `ServicesTest.php` tagged `->issue(163)` so `HarnessTest` still finds the reference. Update the docblock in `Fixture/.../RecordBookPublished.php` too. This is the only test that proves the module binding activates in a booted app (queue/module.php -> BindingRegistry -> lazy `has()`/`get()` in the dispatcher).

6. **The Worker round trip test can pass without serializing anything (003).** `FakeQueue::pop()` returns the same object that was pushed. Fix: the round trip has to serialize the pushed job (`$job->serialize()` / `Job::unserialize()`, or a storing queue) so it proves the event and envelope survive serialization.

7. **Architecture test scope (001).** If it scans `packages/core` including tests, it fails until (2) is done. Pin it to the `Marko\Core` namespace / `packages/core/src`.

8. **Docs list incomplete (004).** `packages/docs-markdown/docs/packages/core.md:387` also describes async. Behaviour changes to document: with queue-sync, an async observer's exception now comes out of `dispatch()` wrapped in `JobFailedException`; an empty encryption key now fails at dispatch time (`JobEnvelope::wrap`); marko/queue installed without a driver throws queue's `NoDriverException` on the first async dispatch.

## Minor (Nice to address)

- `Container::has()` change also affects `Container::call()` (line 122): nullable params registered via `instance()` will now receive the instance instead of null. This is more correct but changes behaviour.
- `SendNotificationJob`/`DispatchWebhookJob` have the same container-serialization problem as (4) on final failure. Out of scope here; worth a follow-up issue.
- The SyncQueue e2e test fits better in `packages/queue-sync/tests` (queue-sync depends on queue, not the reverse).
- There is no per-observer queue name/connection. Passing the full `ObserverDefinition` leaves room to add one later.

## Questions for the Team

- Should an async observer's failure under queue-sync propagate to the `dispatch()` caller (current SyncQueue semantics) or be swallowed/logged?
- Should `marko/testing` ship a `FakeAsyncObserverDispatcher` for app tests?
