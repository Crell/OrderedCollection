# Changelog

All notable changes to `OrderedCollection` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 2.0.0 - YYYY-MM-DD

### Added
- Added a new MultiOrderedCollection sorter. This version is topological-sort centric, and therefore allows multiple before/after directives per item, in addition to priority.
- OrderedCollection and MultiOrderedCollection have a common parent interface, to ease transitioning from one to the other.

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- In OrderedCollection, adding before/after a non-existent other item no longer throws an exception.  Instead, it is ignored and the priority of the item set to 0.  This is more stable in case of optional dependencies, as well as consistent with how MultiOrderedCollection works.

### Security
- Nothing


## 1.0.0 - 2023-03-25

### Added
- Split off from Crell/Tukio.

### Deprecated
- Nothing

### Fixed
- Nothing

## NEXT - YYYY-MM-DD

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
