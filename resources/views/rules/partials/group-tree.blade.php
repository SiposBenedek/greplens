@foreach ($groups as $group)
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $group->id === $activeGroup ? '' : 'collapsed' }}"
                type="button" data-bs-toggle="collapse" data-bs-target="#group-{{ $group->id }}">
                <span class="me-auto">{{ $group->name }}</span>
                <span class="badge me-2">{{ $group->rules->count() }}</span>
            </button>
        </h2>

        <div id="group-{{ $group->id }}"
            class="accordion-collapse collapse {{ $group->id === $activeGroup ? 'show' : '' }}">
            <div class="accordion-body">
                {{-- Rules in this group --}}
                <ul class="list-unstyled mb-0">
                    @foreach ($group->rules as $rule)
                        <li>
                            <a href="?group={{ $group->id }}&amp;rule={{ $rule->id }}"
                                class="sidebar-rule-link {{ $rule->id === $activeRule ? 'active' : '' }}">
                                {{ $rule->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Recursive groups --}}
                @if ($group->children->isNotEmpty())
                    <div class="accordion accordion-flush ms-2 mt-1" id="ruleGroupAccordion-{{ $group->id }}">
                        @include('rules.partials.group-tree', ['groups' => $group->children])
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach