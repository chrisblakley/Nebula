window.performance.mark('(Nebula) Inside /admin-modules/helpers.js');

//Ensure all links in CF7 submission tables open in a new tab
jQuery('.nebula-cf7-submissions a').attr('target', '_blank').attr('rel', 'noopener noreferrer');

//Show relative times in title tooltips
if ( jQuery('.relative-date-tooltip').length ){
	let pageLoadTime = new Date();

	jQuery(document).on('mouseover', '.relative-date-tooltip', function(){
		let relativeDate = pageLoadTime; //Default to page load time

		//Use a provided date if available
		if ( jQuery(this).attr('data-date') ){
			relativeDate = new Date(parseInt(jQuery(this).attr('data-date'))*1000);
		}

		jQuery(this).attr('title', nebula.timeAgo(relativeDate)); // Update the title to show relative time
	});
}

//Notify for possible duplicate post slug
nebula.uniqueSlugChecker = function(){
	if ( jQuery('.edit-post-post-link__link-post-name').length ){
		if ( jQuery('.edit-post-post-link__link-post-name').text().match(/(-\d+)\/?$/) ){
			jQuery('a.edit-post-post-link__link').css('color', 'red');
			jQuery('.edit-post-post-link__preview-label').html('<span title="This likely indicates a duplicate post, but will not prevent saving or publishing." style="cursor: help;">Possible duplicate:</span>');
		}
	}
};

//AI Launchpad Prompt Helpers
if ( jQuery('#nebula-ai').length ){
	if ( window.wp && wp.data && wp.data?.select ){
		jQuery('#ai-prompt-selection').on('change', function(e){
			jQuery('#ai-prompt-explanation small').addClass('hidden');
			jQuery('#ai-prompt-explanation #ai-' + jQuery('#ai-prompt-selection').val()).removeClass('hidden');
		});

		jQuery('#ai-prompt-launcher').on('click', function(e){
			e.preventDefault();
			nebula.promptLaunchpad(jQuery('#ai-prompt-selection').val());
			return false;
		});
	} else {
		jQuery('#nebula-ai').remove();
	}
}

//"Tokenless" method of coping a prompt to the clipboard and opening the AI tool in a new tab
nebula.promptLaunchpad = function(type='content-review'){
	if ( !type ){
		type = 'content-review';
	}

	if ( window.wp && wp.data && wp.data?.select ){
		const postTitle = wp.data.select('core/editor').getEditedPostAttribute('title');
		const blocks = wp.data.select('core/block-editor').getBlocks();
		const headings = blocks.filter(b => b.name === 'core/heading').map(b => b.attributes.content).filter(Boolean); //Get the headings from the post content
		const introParagraph = (blocks.find(b => b.name === 'core/paragraph' && b.attributes?.content)?.attributes.content) || ''; //Get the first paragraph from the post content

		//This gets the rest of the content
		const cleanContent = wp.blocks.parse(wp.data.select('core/editor').getEditedPostAttribute('content'))
			.map(b => b.attributes?.content)
			.filter(Boolean)
			.join('\n\n');

		let prompt = '';

		if ( type.includes('description') ){
			type = 'meta description';
		} else if ( type.includes('title') ){
			type = 'title';
		}

		if ( type.includes('review') ){
			prompt += `Review the following website post content. Analyze and provide actionable recommendations for improvement in the following areas:

1. **SEO** – Assess keyword usage, meta title potential, headings, internal/external linking, readability for search engines, and opportunities for improved rankings.
2. **UX** – Check clarity, scannability, mobile-friendliness of the text, accessibility considerations, and whether the tone matches the target audience.
3. **Engagement** – Evaluate if the content hooks the reader early, maintains interest, uses clear calls to action, and encourages sharing or discussion.
4. **Overall quality** – Point out any grammar or style issues, redundancy, or missing information that could improve trust and authority.

In addition, identify **any other issues, opportunities, or observations** that may improve the content’s effectiveness, even if they fall outside the categories above. This includes potential technical, legal, or brand-consistency considerations.

Present feedback as a **bullet-point list**, with specific examples from the text and suggested rewrites where relevant.

**Title:** ` + postTitle + `
**Body:** ` + cleanContent;
		}

		else if ( type.includes('keyword') ){
			prompt += `You are an SEO strategist. Based on the following WordPress post title and body, recommend keyword targets to add or strengthen so the post better satisfies search intent and expands topical coverage.

### Output rules (important)
- Use **Markdown only**.
- **Do not** return JSON or code blocks.
- Use the exact section order and table formats below.
- No fabricated metrics (e.g., search volume, difficulty). Prioritize by relevance, intent fit, and gap vs. current content.
- Combine near-duplicates under a single canonical term.
- Keep US English unless the content indicates otherwise.

---

# 1) Executive summary (short)
State the primary topic(s), user intents covered/missed, and the biggest topical gaps in 3–5 bullet lines.

---

# 2) Current keyword coverage
Provide a single table:

| Term / Phrase | Coverage (covered / partial / missing) | Notes (where it appears or gap) |
|---|---|---|

---

# 3) Keyword clusters (5–10 clusters)
For each cluster, add a level-2 heading with the cluster name and include **one table per cluster**:

## Cluster: [Concise cluster name]
| Term (Primary + Supporting) | Intent (Info / Nav / Comm / Trans) | Priority (High / Med / Low) | Coverage (covered / partial / missing) | Placement (H2/H3/para/bullets/FAQ/img alt) | Anchor Text (if linking) | Internal Link Ideas (slugs) | External Link Ideas (source types) | Snippet Angle (definition / steps / table / comparison) | Rationale (1–2 sentences) |
|---|---|---|---|---|---|---|---|---|---|

Guidance:
- Include semantic entities (brands, tools, places, people) where relevant.
- “Internal Link Ideas” should be 1–3 plausible slugs (e.g., /category/analytics, /guide/keyword-research).
- “External Link Ideas” should be resource **types** (e.g., government stat, industry report, vendor docs), not fabricated URLs.

---

# 4) SERP opportunities & schema
Provide a compact table:

| Opportunity | How to Target in This Post |
|---|---|
| Featured Snippet | e.g., 40–60 word definition under H2, use exact-match term |
| People Also Ask | e.g., add Q/A under FAQ with concise 1–2 sentence answers |
| Images / Video | e.g., comparison diagram; short explainer video |
| Local Pack (if relevant) | e.g., add NAP references and location-modified terms |
| Others (as applicable) | ... |

Then list **schema recommendations** inline: **Schema:** FAQPage, HowTo, Organization (adjust as relevant).

---

# 5) PAA-style questions (5–15)
A numbered list of the top People-Also-Ask questions mapped to the clusters. Keep each question crisp and intent-focused.

---

# 6) Outline adjustments
Provide a table describing exactly where to add new sections:

| Add After (existing H2/H3 or “intro”) | New Heading | What to Cover (1–2 lines) | Which Cluster(s) |
|---|---|---|---|

---

# 7) Top 10 keywords to implement now
Provide a final prioritization table to make this immediately actionable:

| Term | Why It Matters (1–2 lines) | Best Placement | Internal Link Anchor Text |
|---|---|---|---|

**Title:** ` + postTitle + `
**Body:** ` + cleanContent;

		}

		else if ( type.includes('ideas') ){
			prompt += `Based on the following website post title and body copy, suggest additional content ideas that could enhance the post's value, reach, and SEO performance.

Include ideas such as:
- New sections or subsections
- Additional headings or subheadings
- Related topics, subtopics, or questions to address
- Examples, case studies, or statistics to add
- Internal and external linking opportunities
- Multimedia suggestions (images, infographics, videos, etc.)
- FAQ entries relevant to the topic

Focus on:
- Increasing topical depth and coverage for SEO
- Addressing likely user search intent
- Making the content more engaging and shareable
- Aligning with relevant keywords, semantic terms, and trending queries
- Providing value that competitors might be missing

Also, include **any other ideas** that could improve the post’s performance, even if they fall outside the categories above.

**Title:** ` + postTitle + `
**Body:** ` + cleanContent;
		}

		else {
			prompt += `Create an improved, SEO-friendly ` + type + ` based on the following title and body copy. The ` + type + ` should be clear, engaging, and encourage clicks while accurately reflecting the content.  If helpful, incorporate relevant keywords and trending search terms without sounding forced.

**Current Title:** ` + postTitle + `
**Body:** ` + cleanContent;
		}

		//Copy the prompt to the clipboard and open ChatGPT
		navigator.clipboard.writeText(prompt).then(function(){
			window.open('https://chatgpt.com/', '_blank');
		});
	}
}


//Allow tab character in textareas
nebula.pasteIntoInput = function(element, text){
	element.focus();
	var val = element.value;
	if ( typeof element.selectionStart === 'number' ){
		var selStart = element.selectionStart;
		element.value = val.slice(0, selStart) + text + val.slice(element.selectionEnd);
		element.selectionEnd = element.selectionStart = selStart + text.length;
	} else if ( typeof document.selection !== 'undefined' ){
		var textRange = document.selection.createRange();
		textRange.text = text;
		textRange.collapse(false);
		textRange.select();
	}
};

nebula.allowTabChar = function(element){
	jQuery(element).on('keydown', function(e){
		if ( e.key === 'Tab' ){
			nebula.pasteIntoInput(this, '\t');
			return false;
		}
	});
};

jQuery.fn.allowTabChar = function(){
	if ( this.jquery ){
		this.each(function(){
			if ( this.nodeType === 1 ){
				var nodeName = this.nodeName.toLowerCase();
				if ( nodeName === 'textarea' || (nodeName === 'input' && this.type === 'text') ){
					nebula.allowTabChar(this);
				}
			}
		});
	}

	return this;
};

//Countdown any cooldown timers (such as when Sass processing is thresholded)
//Note: this function is defined in both /modules/helpers.js and /admin-modules/helpers.js
nebula.initCooldowns = function(){
	jQuery('[data-cooldown]').each(function(){
		let $oThis = jQuery(this);
		let timeleft = parseInt($oThis.attr('data-cooldown'));
		let cooldownTimer = setInterval(function(){
			timeleft--;

			let units = '';
			if ( $oThis.attr('data-units') && $oThis.attr('data-units').includes('second') ){
				units = ( timeleft === 1 )? ' second' : ' seconds';
			} else if ( $oThis.attr('data-units') && $oThis.attr('data-units') == 's' ){
				units = 's';
			}

			let output = timeleft + units;
			if ( $oThis.attr('data-parenthesis') ){
				output = '(' + timeleft + units + ')';
			}

			$oThis.text(output);

			if ( timeleft <= 0 ){
				$oThis.parent().parent().find('.cooldown-wait').addClass('hidden');
				$oThis.parent().parent().find('.cooldown-again').removeClass('hidden');

				clearInterval(cooldownTimer);
			}
		}, 1000);
	});
};