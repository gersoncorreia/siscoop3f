---
name: context-engineering
description: Master context engineering for AI agent systems. Optimize token usage and reasoning quality.
---

# Context Engineering (LEAN)

**Goal:** Maximize reasoning via curated, high-signal tokens.

## Rules of Context

1. **Quality > Quantity:** Load only relevant context (just-in-time).
2. **Attention Positional:** Critical info at the BEGINNING/END (avoid middle).
3. **Compaction:** Trigger at 70-80% token utilization.
4. **Tool Design:** Clear, concise descriptions. Optimize for tokens-per-task.

## The Strategy

- **Select:** retrieval/filtering instead of full-file reads.
- **Compress:** Summarize long sessions into compact bullet points.
- **Isolate:** Partition complex work across sub-agents to avoid context poisoning.

## References

- `references/optimization.md` - (Compaction, caching, masking)
- `references/degradation.md` - (Debugging failures, lost-in-middle)
- `references/memory.md` - (Persistence and knowledge graphs)
- `references/multi-agent.md` - (Coordination patterns)