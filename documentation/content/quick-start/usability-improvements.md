---
title: (Temp) Usability Improvements
menu:
  main:
    parent: 'quick-start'
    weight: 80
---

## Quick changes:
- [ ] Rename the respective Configuration tabs to "Team Configuration" and "Flow Configuration" to avoid confusion
- [ ] Make form labels consistent, title case would be preferable
- [ ] Check the content of assistive text to make sure it is well written
- [ ] Add "action prompt" to Registries tab when no Registry created (as per Clusters tab)
- [ ] Remove Github tokens tab (obsolete)
- [ ] Return a user to the Flow overview tab when they manually create a Tide, as it updates dynamically wheras the Tide page is static unless you refresh it

## Bigger changes:
- [ ] Consider renaming Teams to Projects - a Team is currently much wider in scope than what people commonly think a team to be, which means that they need to "relearn" the definition. 
- [ ] Consider adding tabs at top level view to expose linked repos and linked accounts as this is buried in the account settings
- [ ] Consider changing "action prompt" in Team tab - currently it prompts to immediately create a Flow after a team is created - instead it could sequentially prompt to:

    1. configure linked repo
    2. configure Cluster
    3. configure Registry
    4. then finally prompt to create Flow
    
## FE fixes:
- [ ] Header changes height when you adjust browser width, partialy obscuring logo
- [ ] Information spills out of container box on Environment page due to showing full tide ID 