# Changelog

All notable changes to `livewire-comments` will be documented in this file

## 2.0.9 - 2026-08-05

- Add upvote/downvote controls via Livewire `vote` action
- Improve attachment image lightbox handling
- Improve comment card borders and markdown prose (quotes, code blocks)

## 2.0.7 - 2026-08-04

- Fix upload event payload shape: dispatch named `files:` so Alpine gets real FileResource metadata (image thumbnails / filenames)
- Insert-image path inserts markdown again; paperclip keeps attachment chips with previews
- Harden afterUpload against legacy positional `[{ files: [...] }]` wrappers that rendered generic "file" chips and caused submit 500s

## 2.0.6 - 2026-08-04

- Fix Livewire image upload crashing after temp upload (`array_values()` on FileResource collection), which surfaced as a CSRF/419 “page expired” dialog

## 2.0.5 - 2026-08-04

- Version bump aligned with vgcomments 2.0.5 StopForumSpam / store-message hotfix

## 2.0.4 - 2026-08-04

- Version bump aligned with vgcomments / blade-comments guest validation hotfix


## 2.0.3 - 2026-08-04

- Redesign Livewire comments UI with emoji reactions picker
- Improve nested reply threads, alerts, and forms
- Refresh compiled CSS/JS assets

## 1.0.0 - 201X-XX-XX

- initial release
