[updates]

	[day]
		<div class="updates">
			<a href="javascript:;" onclick="$(this).next().toggle()"><h4>[date-prefix]{date-prefix}, [/date-prefix]{date}</h4></a>
			<ul[not-day-1][not-day-2] style="display:none"[/not-day-2][/not-day-1]>
				{updates}
			</ul>
		</div>
	[/day]

[/updates]

[update]

	<li>
		<a href="{full-link}" style="display: block;">

			<div>
				<b>{title}</b>
				[translation]
					({translation})
				[/translation]
				[quality]
					[{quality}]
				[/quality]
			</div>

			<div>
				[season]{season} сезон[/season]
				[episodes]{episodes} серия[/episodes]
			</div>

		</a>
	</li>

[/update]