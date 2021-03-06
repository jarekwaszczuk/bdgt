<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Bill::flushEventListeners();
    }

    public function testIndexDisplaysAllBills()
    {
        $user = factory(User::class)->create();
        $bills = factory(Bill::class, 3)->create([
            'user_id' => $user->id,
        ]);

        $self = $this->actingAs($user)
            ->get(route('bills.index'))
            ->assertStatus(200);

        $bills->each(function ($bill) use ($self) {
            $self->assertSee($bill->label);
        });
    }

    public function testShowDisplaysAssociatedBill()
    {
        $bill = factory(Bill::class)->states('with_user')->create();

        $this->actingAs($bill->user)
            ->get(route('bills.show', [ 'bill' => $bill->id ]))
            ->assertStatus(200)
            ->assertSee($bill->label);
    }

    public function testCannotViewAnotherUsersBill()
    {
        $bill = factory(Bill::class)->states('with_user')->create();

        $this->actingAs(factory(User::class)->create())
            ->get(route('bills.show', [ 'bill' => $bill->id ]))
            ->assertNotFound();
    }

    public function testStorePersistsNewBillAndRedirects()
    {
        $bill = factory(Bill::class)->make();

        $this->actingAs(factory(User::class)->create())
            ->post(route('bills.store', $bill->toArray()))
            ->assertStatus(302);

        $this->assertDatabaseHas('bills', $bill->toArray());
    }

    public function testUnsuccessfulStoreRedirectsWithError()
    {
        $this->actingAs(factory(User::class)->create())
            ->post(route('bills.store', []))
            ->assertStatus(302)
            ->assertSessionHas('errors');
    }

    public function testUpdatePersistsChangesAndRedirects()
    {
        $bill = factory(Bill::class)->states('with_user')->create();
        $bill->amount = 500;

        $this->actingAs($bill->user)
            ->put(route('bills.update', [ 'bill' => $bill->id ]), $bill->toArray())
            ->assertRedirect(route('bills.show', [ 'bill' => $bill->id ]));
    }

    public function testCannotUpdateAnotherUsersBill()
    {
        $bill = factory(Bill::class)->states('with_user')->create();

        $this->actingAs(factory(User::class)->create())
            ->put(route('bills.update', [ 'bill' => $bill->id ]), [])
            ->assertNotFound();
    }

    public function testDeleteDeletesAndRedirectsToIndex()
    {
        $bill = factory(Bill::class)->states('with_user')->create();

        $this->actingAs($bill->user)
            ->delete(route('bills.destroy', $bill->id))
            ->assertRedirect(route('bills.index'));

        $this->assertDatabaseMissing('bills', [ 'id' => $bill->id ]);
    }

    public function testCannotDeleteAnotherUsersBill()
    {
        $bill = factory(Bill::class)->states('with_user')->create();

        $this->actingAs(factory(User::class)->create())
            ->delete(route('bills.destroy', $bill->id))
            ->assertNotFound();
    }
}
