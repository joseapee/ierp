# UI Action Button Loading State

## Rule
All UI action buttons that trigger a request to the backend **must** implement a loading state. This applies to all interactive buttons in Blade, Livewire, or Vue components that initiate backend actions (e.g., save, submit, confirm, convert, next step, etc.).

## Implementation Pattern
- **Livewire Blade Components:**
  - Use `wire:loading.attr="disabled"` on the button to prevent double submissions.
  - Show a spinner or loading text using `wire:loading` and `wire:target` for the specific action.
  - Example:
    ```blade
    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
        Save
    </button>
    ```
- **Other Frameworks (e.g., Vue):**
  - Use a reactive `loading` state to disable the button and show a spinner or loading text while the request is in progress.

## Scope
- Applies to all UI action buttons that trigger backend requests.
- Applies to all frontend technologies used in this project (Blade, Livewire, Vue, etc.).
- This is a **hard rule** and must be enforced in all new and updated UI code.

## Rationale
- Prevents duplicate requests and accidental double submissions.
- Provides clear feedback to users that an action is in progress.

## Example Prompts
- "Add a loading state to the submit button."
- "Ensure all Livewire action buttons use wire:loading."
- "Refactor this Vue button to show a spinner while saving."

## Related Customizations to Consider Next
- Enforce consistent button styling across all modules.
- Require confirmation dialogs for destructive actions.
- Standardize error message display for failed backend requests.
