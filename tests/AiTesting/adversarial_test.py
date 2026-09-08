# tests/AiTesting/test_adversarial.py
from engine import check_rules
from Testing import RULES
import json

with open('tests/AiTesting/adversarial_excerpts.json') as f:
    data = json.load(f)

print("Testing adversarial excerpts...")
for item in data['adversarial_excerpts']:
    failures, rule_ids = check_rules(RULES, item, item['excerpt'])
    if failures:
        print(f"❌ FAIL: {item['excerpt']}...")
        print(f"   {failures}")
    else:
        print(f"✅ PASS: {item['excerpt']}...")
