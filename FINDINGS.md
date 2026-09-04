# FINDINGS

## 1. What I Fixed

- Increased the article excerpt max length from **50 to 250 characters**, and updated every place in the codebase that checks excerpt length to use the new limit.
- Set a new max length for comments: **500 characters**.

## 2. What Real Data Taught Me

- Real article excerpts needed a higher length limit than originally set — 250 characters better reflects how excerpts are actually written and used.
- Adjusted the comment length limit to 500 characters after examining project data

## 3. The Comment Harness

- Built a full rule set for comments — **8 rules total**.
- A few rules were adapted from the article excerpt validator and reworked to fit the comment context.
- Real comments taught me that users can submit things like a single word, an emoji, or a link to an image — so the rules needed to specifically catch and block image links, not just generic HTML.
- Added handling to trim excess and trailing whitespace.
- Set the max comment length to 500 characters.
- Derived the rule logic directly from real comment patterns, then wrote rules to match what actually shows up in practice.
- I manually write rules and tested real comment examples using the JSON dataset and verified that the expected invalid cases were rejected.
- Added automated **mutation testing**, **fuzz testing**, and **metamorphic relation testing** to validate the rule set beyond manual checks.

## 4. What I Found That Surprised Me

- Random test data occasionally lands right at the 250-character (excerpt) or 500-character (comment) boundary. When that happens, padding or truncating the text near that boundary *legitimately* flips whether Rule 2 fires — this is expected behavior, not a real bug. Based on the underlying probability, this shows up in roughly **50–90% of test runs**.
- **Fix:** seed the random number generator for reproducible test runs, and exclude Rule 2 from the two boundary-sensitive invariance checks (truncation and whitespace padding).
- The same boundary behavior — and the same fix — applies equally to the comment validator.

## 5. What the Rules Still Can't Catch

- Based on my review, the article excerpt rules cover every case I could identify.
- The comment rules similarly appear to cover the rules that my project have.
- *(This area could use further adversarial testing to be fully confident — see next steps.)*

## 6. What I'd Do Next, and Why

- If a new bug appears — whether in comment validation, excerpt validation, or a new related task — I'll dig into it thoroughly and resolve it at the root cause rather than patching around the symptom.