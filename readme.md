# Phaml
**Very** WIP [haml][haml_home] parser for PHP. I should have realised that [the name had already been taken][og_phaml], but as a) that project's been seemingly abandoned for over five years (there's a promised "new version" slated for Q4 of 2009) and b) I'm not sure I'll even finish this, I'm not too concerned.

## todo
- [x] Phaml objects to HTML text
- [x] parse haml text into a tree of Phaml objects
- [ ] convert tree to HTML text (incl. child els in their proper place)
- [ ] doctype + other special tags
- [ ] meet [haml spec][haml_spec]
- [ ] execute inline PHP with `-` and `=` tags


[haml_home]: http://haml.info
[og_phaml]: http://phaml.sourceforge.net/
[haml_spec]: https://github.com/haml/haml-spec