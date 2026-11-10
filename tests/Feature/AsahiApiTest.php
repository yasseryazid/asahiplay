<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\QuestionBank;
use App\Models\User;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class AsahiApiTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogle(string $id = 'g-123', string $email = 'bunda@example.com'): void
    {
        $g = (new GoogleUser())->map(['id' => $id, 'name' => 'Bunda Raka', 'email' => $email, 'avatar' => 'https://example.com/a.png']);
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($g);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_google_login_creates_parent_account_and_logs_in(): void
    {
        $this->fakeGoogle();

        $this->get('/auth/google/callback')->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'bunda@example.com', 'google_id' => 'g-123']);
    }

    public function test_second_login_reuses_same_account(): void
    {
        $this->fakeGoogle();
        $this->get('/auth/google/callback');
        auth()->logout();
        $this->get('/auth/google/callback');

        $this->assertSame(1, User::count());
    }

    public function test_home_page_renders_boot_data(): void
    {
        $this->get('/')->assertOk()->assertSee('window.ASAHI', false)->assertSee('Masuk dengan Google');
    }

    public function test_api_requires_login(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/children', ['name' => 'Raka', 'grade' => 'k1'])->assertUnauthorized();
    }

    public function test_parent_manages_children_plays_and_stamps(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->putJson('/api/profile', ['parent' => 'Bunda', 'msg' => 'Semangat!', 'consent' => true])
            ->assertOk()->assertJsonPath('user.consented', true)->assertJsonPath('user.parent', 'Bunda');

        $childId = $this->postJson('/api/children', ['name' => 'Raka', 'grade' => 'k1'])
            ->assertCreated()->json('child.id');

        $this->postJson('/api/children', ['name' => 'X', 'grade' => 'k9'])->assertUnprocessable();

        $playId = $this->postJson("/api/children/{$childId}/plays", [
            'lv' => 'k1', 'tk' => 'pai_nabi', 'stars' => 3, 'items' => 10, 'miss' => 0, 'together' => false, 'by' => [0, 0],
        ])->assertCreated()->json('play.id');

        $this->patchJson("/api/plays/{$playId}", ['stamp' => 1])->assertOk()->assertJsonPath('play.stamp', 1);

        $this->getJson("/api/children/{$childId}/plays")
            ->assertOk()
            ->assertJsonCount(1, 'plays')
            ->assertJsonPath('best.k1:pai_nabi', 3);

        $this->deleteJson("/api/children/{$childId}")->assertNoContent();
        $this->assertDatabaseCount('plays', 0);
    }

    public function test_family_cannot_touch_another_familys_child(): void
    {
        $other = User::factory()->create();
        $child = $other->children()->create(['name' => 'Nadia', 'grade' => 'tk']);
        $play = $child->plays()->create(['level' => 'tk', 'topic' => 'hitung', 'stars' => 2]);

        $this->actingAs(User::factory()->create());

        $this->getJson("/api/children/{$child->id}/plays")->assertNotFound();
        $this->postJson("/api/children/{$child->id}/plays", ['lv' => 'tk', 'tk' => 'hitung', 'stars' => 3])->assertNotFound();
        $this->patchJson("/api/children/{$child->id}", ['name' => 'Hacked'])->assertNotFound();
        $this->patchJson("/api/plays/{$play->id}", ['stamp' => 0])->assertNotFound();
        $this->deleteJson("/api/children/{$child->id}")->assertNotFound();

        $this->assertSame('Nadia', $child->fresh()->name);
    }

    public function test_question_banks_seeded_and_iksc_hidden_by_default(): void
    {
        config(['asahi.iksc_public' => false]);
        $this->seed(QuestionBankSeeder::class);

        $this->getJson('/api/banks/PAI_NABI')->assertOk()->assertJsonCount(22, 'questions');
        $this->getJson('/api/banks/IKSC')->assertNotFound();
        $this->get('/banks/IKSC/en_pic1.jpg')->assertNotFound();

        QuestionBank::where('slug', 'IKSC')->update(['is_public' => true]);
        $this->getJson('/api/banks/IKSC')->assertOk()->assertJsonCount(24, 'questions')->assertJsonPath('ordered', true);
    }

    public function test_max_children_per_account(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 6) as $i) {
            $user->children()->create(['name' => "Anak $i", 'grade' => 'k1']);
        }
        $this->actingAs($user)->postJson('/api/children', ['name' => 'Tujuh', 'grade' => 'k2'])->assertStatus(422);
        $this->assertSame(6, Child::count());
    }

    public function test_ecolier_bank_hidden_by_default_and_complete_when_public(): void
    {
        config(['asahi.iksc_public' => false]);
        $this->seed(QuestionBankSeeder::class);

        $this->getJson('/api/banks/IKSC_ECOLIER')->assertNotFound();
        $this->get('/banks/IKSC_ECOLIER/en_taro.jpg')->assertNotFound();

        QuestionBank::where('slug', 'IKSC_ECOLIER')->update(['is_public' => true]);
        $this->getJson('/api/banks/IKSC_ECOLIER')->assertOk()->assertJsonCount(24, 'questions')->assertJsonPath('ordered', true);
        $this->get('/banks/IKSC_ECOLIER/en_taro.jpg')->assertOk();
    }
}
