"""
adversarial.py

Task 8: Find excerpts that beat your rules.
Generates bad excerpts that still pass all current rules.

Usage:
    python tests/AiTesting/adversarial.py
"""

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import json
import random
import string
from datetime import datetime
from collections import Counter

from engine import check_rules, load_cases
from Testing import RULES, VALUE_FIELD, clean_invisible


# ============================================================
# LOAD ARTICLES FOR CONTEXT
# ============================================================

def load_articles():
    """Load real articles from Articles.json for context."""
    file_path = "tests/AiTesting/Articles.json"
    cases, err = load_cases(file_path)
    if err:
        print(f"⚠️  Warning: Could not load articles: {err}")
        print("   Using dummy context...")
        return []
    return cases


# ============================================================
# BADNESS SCORING
# ============================================================

def repetition_score(text):
    """Score repetition (0-100). Higher = more repetitive."""
    words = text.lower().split()
    if len(words) < 3:
        return 0
    counts = Counter(words)
    most_common = counts.most_common(1)[0]
    return min(100, (most_common[1] / len(words)) * 100)


def meaninglessness_score(text):
    """Score meaninglessness (0-100). Higher = more meaningless."""
    words = text.lower().split()
    if not words:
        return 100

    filler = {
        'actually', 'basically', 'literally', 'practically', 'virtually',
        'stuff', 'things', 'something', 'anything', 'everything',
        'very', 'really', 'quite', 'rather', 'somewhat'
    }

    filler_count = sum(1 for w in words if w in filler)
    return min(100, (filler_count / len(words)) * 100)


def padding_score(text):
    """Score padding (0-100). Higher = more padded."""
    words = text.lower().split()
    if not words:
        return 100

    phrases = [
        'in conclusion', 'as stated above', 'as you can see',
        'it should be noted', 'it is important to note',
        'furthermore', 'moreover', 'additionally',
        'in my opinion', 'i think', 'i believe', 'to be honest'
    ]

    count = sum(1 for p in phrases if p in text.lower())
    return min(100, (count / max(1, len(words))) * 100)


def off_topic_score(text, title="", body=""):
    """Score off-topic (0-100). Higher = more off-topic."""
    if not title and not body:
        return 50

    # Extract keywords from article
    context = (title + " " + body).lower().split()
    stopwords = {'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at',
                 'to', 'for', 'of', 'with', 'without', 'by', 'from'}
    context = {w for w in context if w not in stopwords and len(w) > 2}

    if not context:
        return 50

    excerpt_words = set(text.lower().split())
    if not excerpt_words:
        return 100

    overlap = len(excerpt_words & context)
    overlap_ratio = overlap / len(excerpt_words) if excerpt_words else 0
    return max(0, 100 - (overlap_ratio * 100))


def badness_score(text, title="", body=""):
    """Combined badness score (0-100). Higher = worse."""
    scores = {
        'repetition': repetition_score(text),
        'meaninglessness': meaninglessness_score(text),
        'padding': padding_score(text),
        'off_topic': off_topic_score(text, title, body)
    }
    total = sum(scores.values()) / 4
    return total, scores


# ============================================================
# GENERATE BAD EXCERPTS
# ============================================================

def generate_repetitive():
    """Generate repetitive text."""
    words = ['really', 'very', 'quite', 'extremely', 'totally', 'absolutely']
    topics = ['thing', 'stuff', 'something', 'everything', 'nothing']

    pattern = ' '.join(random.choices(words + topics, k=random.randint(3, 5)))
    count = random.randint(3, 8)
    result = []
    for _ in range(count):
        if random.random() < 0.3:
            result.append(random.choice(['and', 'or', 'but', 'so']))
        result.append(pattern)
    return ' '.join(result)


def generate_padded():
    """Generate text with padding phrases."""
    phrases = [
        'In conclusion,', 'As stated above,', 'It should be noted that',
        'Furthermore,', 'Moreover,', 'Additionally,',
        'In my opinion,', 'I think that', 'To be honest,'
    ]
    nouns = ['thing', 'stuff', 'issue', 'matter', 'situation']

    parts = []
    for _ in range(random.randint(3, 6)):
        part = random.choice(phrases) + ' '
        part += random.choice(['the', 'this', 'that']) + ' ' + random.choice(nouns)
        parts.append(part)
    return ' '.join(parts)


def generate_meaningless():
    """Generate meaningless text."""
    fillers = ['actually', 'basically', 'literally', 'practically', 'sort of', 'kind of']
    topics = ['stuff', 'things', 'something', 'anything']
    actions = ['happened', 'occurred', 'took place', 'went on', 'came up']

    parts = []
    for _ in range(random.randint(3, 6)):
        part = random.choice(fillers) + ' '
        part += random.choice(topics) + ' ' + random.choice(actions)
        parts.append(part)
    return ' '.join(parts)


def generate_off_topic():
    """Generate text unrelated to article topic."""
    off_topic = ['weather', 'sports', 'food', 'travel', 'music', 'movies', 'games']
    words = []
    for _ in range(random.randint(10, 30)):
        if random.random() < 0.3:
            words.append(random.choice(off_topic))
        else:
            words.append(''.join(random.choices(string.ascii_lowercase, k=random.randint(3, 8))))
    return ' '.join(words)


def generate_random():
    """Generate random text."""
    words = [''.join(random.choices(string.ascii_lowercase, k=random.randint(3, 8)))
             for _ in range(random.randint(5, 20))]
    return ' '.join(words)


def generate_excerpt(strategy, title="", body=""):
    """Generate excerpt using specified strategy."""
    strategies = {
        'repetitive': generate_repetitive,
        'padded': generate_padded,
        'meaningless': generate_meaningless,
        'off_topic': generate_off_topic,
        'random': generate_random
    }

    text = strategies.get(strategy, generate_random)()

    # Ensure passes length rules
    cleaned = clean_invisible(text)
    while len(cleaned) > 250:
        words = text.split()
        if len(words) <= 1:
            break
        text = ' '.join(words[:-1])
        cleaned = clean_invisible(text)

    # Ensure at least 2 words
    if len(text.split()) < 2:
        text = text + ' more words'

    return text


# ============================================================
# MAIN SEARCH
# ============================================================

def main():
    print("="*70)
    print("TASK 8: Find excerpts that beat your rules")
    print("="*70)

    # Load articles for context
    print("\n📖 Loading articles...")
    articles = load_articles()
    print(f"   Loaded {len(articles)} articles")

    if not articles:
        print("   ⚠️  Using dummy context...")
        articles = [{"title": "Technology", "body": "Technology and innovation"}]

    print("\n🔍 Generating adversarial excerpts...")
    print("   (Generating candidates that pass ALL current rules)")

    candidates = []
    strategies = ['repetitive', 'padded', 'meaningless', 'off_topic', 'random']
    weights = [0.25, 0.25, 0.20, 0.15, 0.15]

    total_generated = 0
    total_passed = 0

    # Generate 2000 candidates
    for i in range(2000):
        article = random.choice(articles)
        title = article.get('title', '')
        body = article.get('body', '')

        strategy = random.choices(strategies, weights=weights)[0]
        text = generate_excerpt(strategy, title, body)

        total_generated += 1

        # Check if passes all rules
        failures, rule_ids = check_rules(RULES, article, text)

        if failures:
            continue

        total_passed += 1

        # Score badness
        score, details = badness_score(text, title, body)

        candidates.append({
            'title': article.get('title', ''),
            'excerpt': text,
            'body': body[:200] + '...' if len(body) > 200 else body,
            'strategy': strategy,
            'badness_score': round(score, 1),
            'scores': {
                'repetition': round(details['repetition'], 1),
                'meaninglessness': round(details['meaninglessness'], 1),
                'padding': round(details['padding'], 1),
                'off_topic': round(details['off_topic'], 1)
            }
        })

        # Progress update
        if (i + 1) % 500 == 0:
            print(f"   Generated {i+1} candidates... found {total_passed} that pass rules")

    # Sort by badness score (highest first)
    candidates.sort(key=lambda x: x['badness_score'], reverse=True)
    top_candidates = candidates[:20]

    # Summary
    print("\n" + "="*70)
    print("📊 SUMMARY")
    print("="*70)
    print(f"Total generated: {total_generated}")
    print(f"Passed all rules: {total_passed}")
    print(f"Best candidates:  {len(top_candidates)}")
    print(f"Pass rate: { (total_passed / total_generated * 100):.1f}%")

    # Show top candidates
    print("\n" + "="*70)
    print("🏆 TOP ADVERSARIAL EXCERPTS (Worst/Best Badness)")
    print("="*70)

    for i, c in enumerate(top_candidates[:10], 1):
        print(f"\n{i}. Badness Score: {c['badness_score']}/100")
        print(f"   Strategy: {c['strategy']}")
        print(f"   Excerpt: {c['excerpt'][:100]}...")
        print(f"   Repetition: {c['scores']['repetition']}")
        print(f"   Meaningless: {c['scores']['meaninglessness']}")
        print(f"   Padding: {c['scores']['padding']}")
        print(f"   Off-topic: {c['scores']['off_topic']}")

    # Save to JSON
    output = {
        'timestamp': datetime.now().isoformat(),
        'summary': {
            'total_generated': total_generated,
            'passed_all_rules': total_passed,
            'top_candidates': len(top_candidates),
            'pass_rate': round(total_passed / total_generated * 100, 1)
        },
        'adversarial_excerpts': top_candidates
    }

    output_path = 'tests/AiTesting/adversarial_excerpts.json'
    with open(output_path, 'w') as f:
        json.dump(output, f, indent=2)

    print("\n" + "="*70)
    print("💾 SAVED")
    print("="*70)
    print(f"File: {output_path}")
    print(f"Total candidates: {len(top_candidates)}")

    print("\n" + "="*70)
    print("📋 NEXT STEPS")
    print("="*70)
    print("\n1. Review adversarial_excerpts.json")
    print("2. For each excerpt, decide:")
    print("   - Can a rule catch it? → Add rule to testing.py")
    print("   - Cannot be caught? → Document why (requires understanding)")
    print("\n3. After adding rules, run:")
    print("   python testing.py tests/AiTesting/Articles.json")
    print("   to verify the excerpts now FAIL")
    print("4. Update your documentation with the honest boundary")


if __name__ == "__main__":
    main()
