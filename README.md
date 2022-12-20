# Content Password TYPO3 extension

## Migration from v0.2.0 to v1.0.0 (EXT:gridelements to EXT:container)

With v1.0.0 this extension does only support EXT:container,
therefore content elements have to be migrated from the
previous gridelements data structure.

```
alter table tt_content add column if not exists tx_container_parent int(11) DEFAULT '0' NOT NULL;
update tt_content set CType = 'contentpassword_container' where CType = 'gridelements_pi1' and tx_gridelements_backend_layout = 'content_password';

update tt_content A set colPos = 200 + tx_gridelements_columns, tx_container_parent = (select l18n_parent from tt_content where uid = A.tx_gridelements_container limit 1) where colPos = -1 and l18n_parent != 0 and tx_gridelements_container in (select uid from tt_content where CType = 'contentpassword_container');
update tt_content set colPos = 200 + tx_gridelements_columns, tx_container_parent = tx_gridelements_container where colPos = -1 and l18n_parent = 0 and tx_gridelements_container in (select uid from tt_content where CType = 'contentpassword_container');

-- Opinionated migration for v0.2.0 compat if you used a fluid_styled_content compatible header via
-- tt_content.gridelements_pi1.20.10.setup.content_password.preCObject =< lib.contentElement
update tt_content set header_layout = 100, frame_class = 'none', sectionIndex = 0 where CType = 'contentpassword_container';
```
