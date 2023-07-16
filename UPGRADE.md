# Upgrade Guide

## v3

### Upgrading from v3.1 to v3.2

- No breaking changes.
- **Warning**: If upgrading directly from `1.0` to `3.2` the `password` rule will change in expected functionality from
  validating the current password to validating the minimum complexity.

### Upgrading from v3.0 to v3.1

- Minimum Laravel version increased from `9.45` to `9.50.2`.

### Upgrading from v2.0 to v3.0

- Minimum Laravel version increased from `9.6` to `9.45`.
- Removed `forEach` rule helper.

## v2

### Upgrading from v1.0 to v2.0

- Minimum Laravel version increased from `8.82` to `9.6`.
- Removed `password` rule helper.
- Renamed `excludeIf` to `excludeIfValue`.
- Renamed `prohibitedIf` to `prohibitedIfValue`.